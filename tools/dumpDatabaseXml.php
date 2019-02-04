<?php

require_once __DIR__ . '/../lib/Core.php';
ini_set('memory_limit', '512M');

$TODAY = date("Y-m-d");
$TODAY_TIMESTAMP = strtotime("$TODAY 00:00:00");
$REMOTE_FOLDER = 'download/xmldump/v5';
$STATIC_FILES = file(Config::STATIC_URL . 'fileList.txt');
$LAST_DUMP = getLastDumpDate($REMOTE_FOLDER);
$LAST_DUMP_TIMESTAMP = $LAST_DUMP ? strtotime("$LAST_DUMP 00:00:00") : null;
$USERS = getActiveUsers();
$FTP = new FtpUtil();

Log::notice("generating dump for $TODAY; previous dump is " . ($LAST_DUMP ? $LAST_DUMP : '-never-'));

if ($LAST_DUMP == $TODAY) {
  Log::error("a dump already exists for $TODAY; aborting");
  die("a dump already exists for $TODAY; aborting\n");
}

dumpSources("$REMOTE_FOLDER/$TODAY-sources.xml.gz");
dumpInflections("$REMOTE_FOLDER/$TODAY-inflections.xml.gz");
dumpAbbrevs("$REMOTE_FOLDER/$TODAY-abbrevs.xml.gz");
dumpDefinitions("SELECT * FROM Definition WHERE sourceId IN (SELECT id FROM Source WHERE canDistribute) AND status = 0 AND modDate < $TODAY_TIMESTAMP",
                "$REMOTE_FOLDER/$TODAY-definitions.xml.gz",
                'dumping definitions');
dumpEntries("SELECT * FROM Entry where modDate < $TODAY_TIMESTAMP",
            "$REMOTE_FOLDER/$TODAY-entries.xml.gz",
            'dumping entries');
dumpLexemes("SELECT * FROM Lexeme where modDate < $TODAY_TIMESTAMP",
           "$REMOTE_FOLDER/$TODAY-lexems.xml.gz",
           'dumping lexemes and inflected forms');
dumpEd("SELECT ed.entryId, ed.definitionId FROM EntryDefinition ed " .
       "JOIN Definition d on d.id = ed.definitionId " .
       "WHERE d.sourceId in (SELECT id FROM Source WHERE canDistribute) " .
       "AND d.status = 0 " .
       "AND ed.modDate < $TODAY_TIMESTAMP " .
       "ORDER BY ed.entryId, ed.id",
       "$REMOTE_FOLDER/$TODAY-edm.xml.gz",
       'dumping entry-definition map');
dumpEl("SELECT el.entryId, el.lexemeId FROM EntryLexeme el " .
       "JOIN Entry e on e.id = el.entryId " .
       "JOIN Lexeme l on l.id = el.lexemeId " .
       "WHERE el.modDate < $TODAY_TIMESTAMP " .
       "AND e.modDate < $TODAY_TIMESTAMP " .
       "AND l.modDate < $TODAY_TIMESTAMP " .
       "ORDER BY el.entryId, el.id",
       "$REMOTE_FOLDER/$TODAY-elm.xml.gz",
       'dumping entry-lexeme map');

if ($LAST_DUMP) {
  dumpDefinitions("SELECT * FROM Definition WHERE sourceId IN (SELECT id FROM Source WHERE canDistribute) " .
                  "AND modDate >= $LAST_DUMP_TIMESTAMP AND modDate < $TODAY_TIMESTAMP",
                  "$REMOTE_FOLDER/$TODAY-definitions-diff.xml.gz",
                  'dumping definitions diff');

  dumpEntries("SELECT * FROM Entry where modDate >= $LAST_DUMP_TIMESTAMP AND modDate < $TODAY_TIMESTAMP",
              "$REMOTE_FOLDER/$TODAY-entries-diff.xml.gz",
              'dumping entries diff');

  dumpLexemes("SELECT * FROM Lexeme where modDate >= $LAST_DUMP_TIMESTAMP AND modDate < $TODAY_TIMESTAMP",
             "$REMOTE_FOLDER/$TODAY-lexems-diff.xml.gz",
             'dumping lexemes and inflected forms diff');

  dumpDiff("$REMOTE_FOLDER/$LAST_DUMP-edm.xml.gz",
           "$REMOTE_FOLDER/$TODAY-edm.xml.gz",
           "$REMOTE_FOLDER/$TODAY-edm-diff.xml.gz",
           'EntryDefinition',
           'dumping entry-definition map diff');

  dumpDiff("$REMOTE_FOLDER/$LAST_DUMP-elm.xml.gz",
           "$REMOTE_FOLDER/$TODAY-elm.xml.gz",
           "$REMOTE_FOLDER/$TODAY-elm-diff.xml.gz",
           'EntryLexem',
           'dumping entry-lexeme map diff');
}

removeOldDumps($REMOTE_FOLDER, $TODAY, $LAST_DUMP);

Log::notice('finished');

/**************************************************************************/

function getActiveUsers() {
  $results = DB::execute("SELECT id, nick FROM User WHERE id IN (SELECT DISTINCT userId FROM Definition)");
  $users = [];
  foreach ($results as $row) {
    $users[$row[0]] = $row[1];
  }
  return $users;
}

function getLastDumpDate($folder) {
  global $STATIC_FILES;

  // Group existing files by date, excluding the diff files
  $map = [];
  foreach ($STATIC_FILES as $file) {
    $matches = [];
    if (preg_match(":^{$folder}/(\\d\\d\\d\\d-\\d\\d-\\d\\d)-[a-z]+.xml.gz:", $file, $matches)) {
      if (array_key_exists($matches[1], $map)) {
        $map[$matches[1]]++;
      } else {
        $map[$matches[1]] = 1;
      }
    }
  }

  // Now check if the most recent date has 8 dump files
  if (count($map)) {
    krsort($map);
    $date = key($map); // First key
    return ($map[$date] == 8) ? $date : null;
  } else {
    return null;
  }
}

function dumpSources($remoteFile) {
  global $FTP;

  Log::info("dumping sources");
  Smart::assign('sources', Model::factory('Source')->order_by_asc('id')->find_many());
  $xml = Smart::fetch('xml/xmldump/sources.tpl');
  $gzip = gzencode($xml);
  $FTP->staticServerPutContents($gzip, $remoteFile);
}

function dumpInflections($remoteFile) {
  global $FTP;

  Log::info("dumping inflections");
  Smart::assign('inflections', Model::factory('Inflection')->order_by_asc('id')->find_many());
  $xml = Smart::fetch('xml/xmldump/inflections.tpl');
  $gzip = gzencode($xml);
  $FTP->staticServerPutContents($gzip, $remoteFile);
}

function dumpAbbrevs($remoteFile) {
  global $FTP;

  Log::info("dumping abbreviations");

  $sourceIds = Model::factory('Abbreviation')
             ->select('sourceId')
             ->distinct()
             ->find_array();
  $sourceIds = array_column($sourceIds, 'sourceId');

  $map = [];
  foreach ($sourceIds as $sourceId) {
    $map[$sourceId] = Abbrev::loadAbbreviations($sourceId);
  }

  Smart::assign('map', $map);
  $xml = Smart::fetch('xml/xmldump/abbrev.tpl');
  $gzip = gzencode($xml);
  $FTP->staticServerPutContents($gzip, $remoteFile);
}

function dumpDefinitions($query, $remoteFile, $message) {
  global $FTP, $USERS;

  DB::setBuffering(false);

  Log::info($message);
  $results = DB::execute($query);
  $tmpFile = tempnam(Config::TEMP_DIR, 'xmldump_');
  $file = gzopen($tmpFile, 'wb9');
  gzwrite($file, "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n");
  gzwrite($file, "<Definitions>\n");
  foreach ($results as $row) {
    $def = Model::factory('Definition')->create($row);
    $def->internalRep = Str::xmlize($def->internalRep);
    Smart::assign('def', $def);
    Smart::assign('nick', $USERS[$def->userId]);
    gzwrite($file, Smart::fetch('xml/xmldump/definition.tpl'));
  }
  gzwrite($file, "</Definitions>\n");
  gzclose($file);
  $FTP->staticServerPut($tmpFile, $remoteFile);
  unlink($tmpFile);

  DB::setBuffering(true);
}

function dumpEntries($query, $remoteFile, $message) {
  global $FTP;

  Log::info($message);
  $results = DB::execute($query);
  $tmpFile = tempnam(Config::TEMP_DIR, 'xmldump_');
  $file = gzopen($tmpFile, 'wb9');
  gzwrite($file, "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n");
  gzwrite($file, "<Entries>\n");
  foreach($results as $row) {
    $entry = Model::factory('Entry')->create($row);
    Smart::assign('entry', $entry);
    gzwrite($file, Smart::fetch('xml/xmldump/entry.tpl'));
  }
  gzwrite($file, "</Entries>\n");
  gzclose($file);
  $FTP->staticServerPut($tmpFile, $remoteFile);
  unlink($tmpFile);
}

function dumpLexemes($query, $remoteFile, $message) {
  global $FTP;

  Log::info($message);
  $results = DB::execute($query);
  $tmpFile = tempnam(Config::TEMP_DIR, 'xmldump_');
  $file = gzopen($tmpFile, 'wb9');
  gzwrite($file, "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n");
  gzwrite($file, "<Lexems>\n");
  foreach($results as $row) {
    $lexeme = Model::factory('Lexeme')->create($row);
    Smart::assign('lexeme', $lexeme);
    gzwrite($file, Smart::fetch('xml/xmldump/lexeme.tpl'));
  }
  gzwrite($file, "</Lexems>\n");
  gzclose($file);
  $FTP->staticServerPut($tmpFile, $remoteFile);
  unlink($tmpFile);
}

function dumpEd($query, $remoteFile, $message) {
  global $FTP;

  Log::info($message);
  $results = DB::execute($query);
  $tmpFile = tempnam(Config::TEMP_DIR, 'xmldump_');
  $file = gzopen($tmpFile, 'wb9');
  gzwrite($file, "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n");
  gzwrite($file, "<EntryDefinition>\n");
  foreach ($results as $row) {
    gzwrite($file, "  <Map entryId=\"{$row[0]}\" definitionId=\"{$row[1]}\"/>\n");
  }
  gzwrite($file, "</EntryDefinition>\n");
  gzclose($file);
  $FTP->staticServerPut($tmpFile, $remoteFile);
  unlink($tmpFile);
}

function dumpEl($query, $remoteFile, $message) {
  global $FTP;

  Log::info($message);
  $results = DB::execute($query);
  $tmpFile = tempnam(Config::TEMP_DIR, 'xmldump_');
  $file = gzopen($tmpFile, 'wb9');
  gzwrite($file, "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n");
  gzwrite($file, "<EntryLexem>\n");
  foreach ($results as $row) {
    gzwrite($file, "  <Map entryId=\"{$row[0]}\" lexemId=\"{$row[1]}\"/>\n");
  }
  gzwrite($file, "</EntryLexem>\n");
  gzclose($file);
  $FTP->staticServerPut($tmpFile, $remoteFile);
  unlink($tmpFile);
}

function dumpDiff($oldRemoteFile, $newRemoteFile, $diffRemoteFile, $elementName, $message) {
  global $FTP;

  Log::info($message);

  // Transfer the files locally
  $oldXml = wgetAndGunzip(Config::STATIC_URL . '/' . $oldRemoteFile);
  $newXml = wgetAndGunzip(Config::STATIC_URL . '/' . $newRemoteFile);
  $output = null;
  exec("diff $oldXml $newXml", $output, $ignored);
  $tmpFile = tempnam(Config::TEMP_DIR, 'xmldump_');
  $file = gzopen($tmpFile, 'wb9');
  gzwrite($file, "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n");
  gzwrite($file, "<{$elementName}>\n");
  foreach ($output as $line) {
    if (substr($line, 0, 2) == '< ') {
      gzwrite($file, preg_replace('/<Map /', '<Unmap ', substr($line, 2)) . "\n"); // Deleted line
    } else if (substr($line, 0, 2) == '> ') {
      gzwrite($file, substr($line, 2) . "\n"); // Added line
    }
  }
  gzwrite($file, "</{$elementName}>\n");
  gzclose($file);
  $FTP->staticServerPut($tmpFile, $diffRemoteFile);
  unlink($tmpFile);
  unlink($oldXml);
  unlink($newXml);
}

// Returns a file name in tempDir pointing to the unzipped file
function wgetAndGunzip($url) {
  $tmpFile = tempnam(Config::TEMP_DIR, 'xmldump_');
  OS::executeAndAssert("wget -q -O $tmpFile.gz $url");
  OS::executeAndAssert("gunzip -f $tmpFile.gz");
  return $tmpFile;
}

// Delete all dumps other than current one and previous one. Keep the diffs
function removeOldDumps($folder, $today, $lastDump) {
  global $FTP, $STATIC_FILES;

  Log::info('removing old dumps');
  foreach ($STATIC_FILES as $file) {
    $matches = [];
    $file = trim($file);
    if (preg_match(":^{$folder}/(\\d\\d\\d\\d-\\d\\d-\\d\\d)-" .
                   "(abbrevs|definitions|entries|inflections|edm|elm|lexems|sources).xml.gz$:",
                   $file, $matches)) {
      $date = $matches[1];
      if ($date != $today && $date != $lastDump) {
        Log::info("  deleting $file");
        $FTP->staticServerDelete($file);
      }
    }
  }
}

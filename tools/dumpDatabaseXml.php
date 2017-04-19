<?php

require_once __DIR__ . '/../phplib/util.php';
ini_set('memory_limit', '512M');

$TODAY = date("Y-m-d");
$TODAY_TIMESTAMP = strtotime("$TODAY 00:00:00");
$REMOTE_FOLDER = 'download/xmldump/v5';
$STATIC_FILES = file(Config::get('static.url') . 'fileList.txt');
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
dumpLexems("SELECT * FROM Lexem where modDate < $TODAY_TIMESTAMP",
           "$REMOTE_FOLDER/$TODAY-lexems.xml.gz",
           'dumping lexems and inflected forms');
dumpEd("SELECT ed.entryId, ed.definitionId FROM EntryDefinition ed " .
       "JOIN Definition d on d.id = ed.definitionId " .
       "WHERE d.sourceId in (SELECT id FROM Source WHERE canDistribute) " .
       "AND d.status = 0 " .
       "AND ed.modDate < $TODAY_TIMESTAMP " .
       "ORDER BY ed.entryId, ed.definitionId",
       "$REMOTE_FOLDER/$TODAY-edm.xml.gz",
       'dumping entry-definition map');

if ($LAST_DUMP) {
  dumpDefinitions("SELECT * FROM Definition WHERE sourceId IN (SELECT id FROM Source WHERE canDistribute) " .
                  "AND modDate >= $LAST_DUMP_TIMESTAMP AND modDate < $TODAY_TIMESTAMP",
                  "$REMOTE_FOLDER/$TODAY-definitions-diff.xml.gz",
                  'dumping definitions diff');

  dumpEntries("SELECT * FROM Entry where modDate >= $LAST_DUMP_TIMESTAMP AND modDate < $TODAY_TIMESTAMP",
              "$REMOTE_FOLDER/$TODAY-entries-diff.xml.gz",
              'dumping entries diff');

  dumpLexems("SELECT * FROM Lexem where modDate >= $LAST_DUMP_TIMESTAMP AND modDate < $TODAY_TIMESTAMP",
             "$REMOTE_FOLDER/$TODAY-lexems-diff.xml.gz",
             'dumping lexems and inflected forms diff');

  dumpEdDiff("$REMOTE_FOLDER/$LAST_DUMP-edm.xml.gz", "$REMOTE_FOLDER/$TODAY-edm.xml.gz", "$REMOTE_FOLDER/$TODAY-edm-diff.xml.gz");
}

removeOldDumps($REMOTE_FOLDER, $TODAY, $LAST_DUMP);

Log::notice('finished');

/**************************************************************************/

function getActiveUsers() {
  $results = DB::execute("SELECT id, nick FROM User WHERE id IN (SELECT DISTINCT userId FROM Definition)");
  $users = array();
  foreach ($results as $row) {
    $users[$row[0]] = $row[1];
  }
  return $users;
}

function getLastDumpDate($folder) {
  global $STATIC_FILES;

  // Group existing files by date, excluding the diff files
  $map = array();
  foreach ($STATIC_FILES as $file) {
    $matches = array();
    if (preg_match(":^{$folder}/(\\d\\d\\d\\d-\\d\\d-\\d\\d)-[a-z]+.xml.gz:", $file, $matches)) {
      if (array_key_exists($matches[1], $map)) {
        $map[$matches[1]]++;
      } else {
        $map[$matches[1]] = 1;
      }
    }
  }

  // Now check if the most recent date has 7 dump files
  if (count($map)) {
    krsort($map);
    $date = key($map); // First key
    return ($map[$date] == 7) ? $date : null;
  } else {  
    return null;
  }
}

function dumpSources($remoteFile) {
  global $FTP;

  Log::info("dumping sources");
  SmartyWrap::assign('sources', Model::factory('Source')->order_by_asc('id')->find_many());
  $xml = SmartyWrap::fetch('xml/xmldump/sources.tpl');
  $gzip = gzencode($xml);
  $FTP->staticServerPutContents($gzip, $remoteFile);
}

function dumpInflections($remoteFile) {
  global $FTP;

  Log::info("dumping inflections");
  SmartyWrap::assign('inflections', Model::factory('Inflection')->order_by_asc('id')->find_many());
  $xml = SmartyWrap::fetch('xml/xmldump/inflections.tpl');
  $gzip = gzencode($xml);
  $FTP->staticServerPutContents($gzip, $remoteFile);
}

function dumpAbbrevs($remoteFile) {
  global $FTP;

  Log::info("dumping abbreviations");
  $sources = AdminStringUtil::loadAbbreviationsIndex();
  $sectionNames = AdminStringUtil::getAbbrevSectionNames();
  $sections = array();

  foreach ($sectionNames as $name) {
    $raw_section = parse_ini_file(util_getRootPath() . "docs/abbrev/{$name}.conf", true);
    $section = array();
    foreach ($raw_section[$name] as $short => $long) {
      $abbrev_info = array('short' => $short, 'long' => $long, 'ambiguous' => false);
      if (substr($short, 0, 1) == "*") {
        $abbrev_info['short'] = substr($short, 1);
        $abbrev_info['ambiguous'] = true;
      }
      $section[] = $abbrev_info;
    }
    $sections[$name] = $section;
  }
  SmartyWrap::assign('sources', $sources);
  SmartyWrap::assign('sections', $sections);
  $xml = SmartyWrap::fetch('xml/xmldump/abbrev.tpl');
  $gzip = gzencode($xml);
  $FTP->staticServerPutContents($gzip, $remoteFile);
}

function dumpDefinitions($query, $remoteFile, $message) {
  global $FTP, $USERS;

  DB::setBuffering(false);

  Log::info($message);
  $results = DB::execute($query);
  $tmpFile = tempnam(Config::get('global.tempDir'), 'xmldump_');
  $file = gzopen($tmpFile, 'wb9');
  gzwrite($file, "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n");
  gzwrite($file, "<Definitions>\n");
  foreach ($results as $row) {
    $def = Model::factory('Definition')->create($row);
    $def->internalRep = AdminStringUtil::xmlizeRequired($def->internalRep);
    SmartyWrap::assign('def', $def);
    SmartyWrap::assign('nick', $USERS[$def->userId]);
    gzwrite($file, SmartyWrap::fetch('xml/xmldump/definition.tpl'));
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
  $tmpFile = tempnam(Config::get('global.tempDir'), 'xmldump_');
  $file = gzopen($tmpFile, 'wb9');
  gzwrite($file, "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n");
  gzwrite($file, "<Entries>\n");
  foreach($results as $row) {
    $entry = Model::factory('Entry')->create($row);
    SmartyWrap::assign('entry', $entry);
    gzwrite($file, SmartyWrap::fetch('xml/xmldump/entry.tpl'));
  }
  gzwrite($file, "</Entries>\n");
  gzclose($file);
  $FTP->staticServerPut($tmpFile, $remoteFile);
  unlink($tmpFile);
}

function dumpLexems($query, $remoteFile, $message) {
  global $FTP;

  Log::info($message);
  $results = DB::execute($query);
  $tmpFile = tempnam(Config::get('global.tempDir'), 'xmldump_');
  $file = gzopen($tmpFile, 'wb9');
  gzwrite($file, "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n");
  gzwrite($file, "<Lexems>\n");
  foreach($results as $row) {
    $lexem = Model::factory('Lexem')->create($row);
    SmartyWrap::assign('lexem', $lexem);
    gzwrite($file, SmartyWrap::fetch('xml/xmldump/lexem.tpl'));
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
  $tmpFile = tempnam(Config::get('global.tempDir'), 'xmldump_');
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

function dumpEdDiff($oldRemoteFile, $newRemoteFile, $diffRemoteFile) {
  global $FTP;

  Log::info('dumping entry-definition map diff');

  // Transfer the files locally
  $oldXml = wgetAndGunzip(Config::get('static.url') . '/' . $oldRemoteFile);
  $newXml = wgetAndGunzip(Config::get('static.url') . '/' . $newRemoteFile);
  $output = null;
  exec("diff $oldXml $newXml", $output, $ignored);
  $tmpFile = tempnam(Config::get('global.tempDir'), 'xmldump_');  
  $file = gzopen($tmpFile, 'wb9');
  gzwrite($file, "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n");
  gzwrite($file, "<EntryDefinition>\n");
  foreach ($output as $line) {
    if (substr($line, 0, 2) == '< ') {
      gzwrite($file, preg_replace('/<Map /', '<Unmap ', substr($line, 2)) . "\n"); // Deleted line
    } else if (substr($line, 0, 2) == '> ') {
      gzwrite($file, substr($line, 2) . "\n"); // Added line
    }
  }
  gzwrite($file, "</EntryDefinition>\n");
  gzclose($file);
  $FTP->staticServerPut($tmpFile, $diffRemoteFile);
  unlink($tmpFile);
  unlink($oldXml);
  unlink($newXml);
}

// Returns a file name in tempDir pointing to the unzipped file
function wgetAndGunzip($url) {
  $tmpFile = tempnam(Config::get('global.tempDir'), 'xmldump_');
  OS::executeAndAssert("wget -q -O $tmpFile.gz $url");
  OS::executeAndAssert("gunzip -f $tmpFile.gz");
  return $tmpFile;
}

// Delete all dumps other than current one and previous one. Keep the diffs
function removeOldDumps($folder, $today, $lastDump) {
  global $FTP, $STATIC_FILES;

  Log::info('removing old dumps');
  foreach ($STATIC_FILES as $file) {
    $matches = array();
    $file = trim($file);
    if (preg_match(":^{$folder}/(\\d\\d\\d\\d-\\d\\d-\\d\\d)-(abbrevs|definitions|inflections|edm|lexems|sources).xml.gz$:", $file, $matches)) {
      $date = $matches[1];
      if ($date != $today && $date != $lastDump) {
        Log::info("  deleting $file");
        $FTP->staticServerDelete($file);
      }
    }
  }
}

?>

<?php

require_once("../phplib/util.php");
smarty_init();

$TODAY = date("Y-m-d");
$TODAY_TIMESTAMP = strtotime("$TODAY 00:00:00");
$FOLDER = util_getRootPath() . '/wwwbase/download/xmldump';
$LAST_DUMP = getLastDumpDate($FOLDER);
$LAST_DUMP_TIMESTAMP = $LAST_DUMP ? strtotime("$LAST_DUMP 00:00:00") : null;
$USERS = getActiveUsers();

log_scriptLog("generating dump for $TODAY; previous dump is " . ($LAST_DUMP ? $LAST_DUMP : '-never-'));

if ($LAST_DUMP == $TODAY) {
  log_scriptLog("a dump already exists for $TODAY; aborting");
  exit(1);
}

dumpSources("$FOLDER/$TODAY-sources.xml.gz");
dumpInflections("$FOLDER/$TODAY-inflections.xml.gz");
dumpAbbrevs("$FOLDER/$TODAY-abbrevs.xml.gz");
dumpDefinitions("SELECT * FROM Definition WHERE sourceId IN (SELECT id FROM Source WHERE canDistribute) AND status = 0 AND modDate < $TODAY_TIMESTAMP",
                "$FOLDER/$TODAY-definitions.xml.gz",
                'dumping definitions');
dumpLexems("SELECT * FROM Lexem where modDate < $TODAY_TIMESTAMP",
           "$FOLDER/$TODAY-lexems.xml.gz",
           'dumping lexems and inflected forms');
dumpLdm("SELECT M.lexemId, M.definitionId FROM LexemDefinitionMap M, Definition D " .
        "WHERE D.id = M.definitionId AND D.sourceId in (SELECT id FROM Source WHERE canDistribute) " .
        "AND D.status = 0 AND M.modDate < $TODAY_TIMESTAMP ORDER BY M.lexemId, M.definitionId",
        "$FOLDER/$TODAY-ldm.xml.gz",
        'dumping lexem-definition map');

if ($LAST_DUMP) {
  dumpDefinitions("SELECT * FROM Definition WHERE sourceId IN (SELECT id FROM Source WHERE canDistribute) " .
                  "AND modDate >= $LAST_DUMP_TIMESTAMP AND modDate < $TODAY_TIMESTAMP",
                  "$FOLDER/$TODAY-definitions-diff.xml.gz",
                  'dumping definitions diff');

  dumpLexems("SELECT * FROM Lexem where modDate >= $LAST_DUMP_TIMESTAMP AND modDate < $TODAY_TIMESTAMP",
             "$FOLDER/$TODAY-lexems-diff.xml.gz",
             'dumping lexems and inflected forms diff');

  dumpLdmDiff("$FOLDER/$LAST_DUMP-ldm.xml.gz", "$FOLDER/$TODAY-ldm.xml.gz", "$FOLDER/$TODAY-ldm-diff.xml.gz");
}

removeOldDumps($FOLDER, $TODAY, $LAST_DUMP);

/**************************************************************************/

function getActiveUsers() {
  $results = db_execute("SELECT id, nick FROM User WHERE id IN (SELECT DISTINCT userId FROM Definition)");
  $users = array();
  while (!$results->EOF) {
    $users[$results->fields[0]] = $results->fields[1];
    $results->MoveNext();
  }
  return $users;
}

function getLastDumpDate($folder) {
  $files = scandir($folder, 1); // descending
  foreach ($files as $file) {
    $matches = array();
    if (preg_match('/^(\\d\\d\\d\\d-\\d\\d-\\d\\d)-abbrevs.xml.gz$/', $file, $matches)) {
      $candidate = $matches[1];
      if (file_exists("$folder/$candidate-abbrevs.xml.gz") &&
          file_exists("$folder/$candidate-definitions.xml.gz") &&
          file_exists("$folder/$candidate-inflections.xml.gz") &&
          file_exists("$folder/$candidate-ldm.xml.gz") &&
          file_exists("$folder/$candidate-lexems.xml.gz") &&
          file_exists("$folder/$candidate-sources.xml.gz")) {
        return $candidate;
      }
    }
  }
  return null;
}

function dumpSources($fileName) {
  log_scriptLog("dumping sources");
  smarty_assign('sources', db_find(new Source(), '1 ORDER BY id'));
  $xml = smarty_fetch('xmldump/sources.ihtml');
  file_put_contents($fileName, gzencode($xml));
}

function dumpInflections($fileName) {
  log_scriptLog("dumping inflections");
  smarty_assign('inflections', db_find(new Inflection(), '1 ORDER BY id'));
  $xml = smarty_fetch('xmldump/inflections.ihtml');
  file_put_contents($fileName, gzencode($xml));
}

function dumpAbbrevs($fileName) {
  log_scriptLog("dumping abbreviations");
  $raw_abbrevs = text_loadRawAbbreviations();
  $sources = array();
  $sections = array();
  foreach ($raw_abbrevs as $name => $raw_section) {
    if ($name == "sources") {
      // the index of sources
      foreach ($raw_section as $id => $source) {
        $sources[$id] = preg_split('/, */', $source);
      }
    } else {
      // a single source
      $section = array();
      foreach ($raw_section as $short => $long) {
        $abbrev_info = array('short' => $short, 'long' => $long, 'ambiguous' => false);
        if (substr($short, 0, 1) == "*") {
          $abbrev_info['short'] = substr($short, 1);
          $abbrev_info['ambiguous'] = true;
        }
        $section[] = $abbrev_info;
      }
      $sections[$name] = $section;
    }
  }
  smarty_assign('sources', $sources);
  smarty_assign('sections', $sections);
  $xml = smarty_fetch('xmldump/abbrev.ihtml');
  file_put_contents($fileName, gzencode($xml));
}

function dumpDefinitions($query, $fileName, $message) {
  global $USERS;

  log_scriptLog($message);
  $results = db_execute($query);
  $file = gzopen($fileName, 'wb9');
  gzwrite($file, "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n");
  gzwrite($file, "<Definitions>\n");
  while (!$results->EOF) {
    $def = new Definition();
    $def->set($results->fields);
    $def->internalRep = text_xmlizeRequired($def->internalRep);
    smarty_assign('def', $def);
    smarty_assign('nick', $USERS[$def->userId]);
    gzwrite($file, smarty_fetch('xmldump/definition.ihtml'));
    $results->MoveNext();
  }
  gzwrite($file, "</Definitions>\n");
  gzclose($file);
}

function dumpLexems($query, $fileName, $message) {
  log_scriptLog($message);
  $results = db_execute($query);
  $file = gzopen($fileName, 'wb9');
  gzwrite($file, "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n");
  gzwrite($file, "<Lexems>\n");
  while (!$results->EOF) {
    $lexem = new Lexem();
    $lexem->set($results->fields);
    smarty_assign('lexem', $lexem);
    smarty_assign('ifs', InflectedForm::loadByLexemId($lexem->id));
    gzwrite($file, smarty_fetch('xmldump/lexem.ihtml'));
    $results->MoveNext();
  }
  gzwrite($file, "</Lexems>\n");
  gzclose($file);
}

function dumpLdm($query, $fileName, $message) {
  log_scriptLog($message);
  $results = db_execute($query);
  $file = gzopen($fileName, 'wb9');
  gzwrite($file, "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n");
  gzwrite($file, "<LexemDefinitionMap>\n");
  while (!$results->EOF) {
    gzwrite($file, sprintf("  <Map lexemId=\"%s\" definitionId=\"%s\"/>\n", $results->fields[0], $results->fields[1]));
    $results->MoveNext();
  }
  gzwrite($file, "</LexemDefinitionMap>\n");
  gzclose($file);
}

function dumpLdmDiff($oldFileName, $newFileName, $diffFileName) {
  log_scriptLog('dumping lexem-definition map diff');
  $tmpFile1 = tempnam('/tmp', 'ldm');
  $tmpFile2 = tempnam('/tmp', 'ldm');
  os_executeAndAssert("gunzip -c $oldFileName > $tmpFile1");
  os_executeAndAssert("gunzip -c $newFileName > $tmpFile2");
  $output = null;
  exec("diff $tmpFile1 $tmpFile2", $output, $exit_code);
  $file = gzopen($diffFileName, 'wb9');
  gzwrite($file, "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n");
  gzwrite($file, "<LexemDefinitionMap>\n");
  foreach ($output as $line) {
    if (substr($line, 0, 2) == '< ') {
      gzwrite($file, preg_replace('/<Map /', '<Unmap ', substr($line, 2)) . "\n"); // Deleted line
    } else if (substr($line, 0, 2) == '> ') {
      gzwrite($file, substr($line, 2) . "\n"); // Added line
    }
  }
  gzwrite($file, "</LexemDefinitionMap>\n");
  gzclose($file);
  unlink($tmpFile1);
  unlink($tmpFile2);
}

// Delete all dumps other than current one and previous one. Keep the diffs
function removeOldDumps($folder, $today, $lastDump) {
  log_scriptLog('removing old dumps');
  foreach (scandir($folder) as $file) {
    if (preg_match('/^(\\d\\d\\d\\d-\\d\\d-\\d\\d)-(abbrevs|definitions|inflections|ldm|lexems|sources).xml.gz$/', $file, $matches)) {
      $date = $matches[1];
      if ($date != $today && $date != $lastDump) {
        log_scriptLog("  deleting $file");
        unlink($file);
      }
    }
  }
}

?>

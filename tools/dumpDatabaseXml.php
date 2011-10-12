<?php

require_once("../phplib/util.php");
smarty_init();

$TODAY = date("Ymd");
$FOLDER = util_getRootPath() . '/wwwbase/download/xmldump';
$LAST_DUMP = getLastDumpDate($FOLDER);

log_scriptLog("generating dump for $TODAY; previous dump is $LAST_DUMP");

// dump sources table
log_scriptLog("dumping sources");
smarty_assign('sources', db_find(new Source(), '1 ORDER BY id'));
$sources_xml = smarty_fetch('xmldump/sources.ihtml');
file_put_contents("$FOLDER/$TODAY-sources.xml.gz", gzencode($sources_xml));

// dump inflections table
log_scriptLog("dumping inflections");
smarty_assign('inflections', db_find(new Inflection(), '1 ORDER BY id'));
$inflections_xml = smarty_fetch('xmldump/inflections.ihtml');
file_put_contents("$FOLDER/$TODAY-inflections.xml.gz", gzencode($inflections_xml));

// dump abbrev table
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
$abbrevs_xml = smarty_fetch('xmldump/abbrev.ihtml');
file_put_contents("$FOLDER/$TODAY-abbrevs.xml.gz", gzencode($abbrevs_xml));

// dump definitions table
log_scriptLog("dumping definitions");
$users = getActiveUsers();
$defResults = db_execute("SELECT * FROM Definition WHERE sourceId IN (SELECT id FROM Source WHERE canDistribute) AND status = 0");
$defsFile = gzopen("$FOLDER/$TODAY-definitions.xml.gz", 'wb9');
gzwrite($defsFile, "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n");
gzwrite($defsFile, "<Definitions>\n");
while (!$defResults->EOF) {
  $def = new Definition();
  $def->set($defResults->fields);
  $def->internalRep = text_xmlizeRequired($def->internalRep);
  smarty_assign('def', $def);
  smarty_assign('nick', $users[$def->userId]);
  $def_xml = smarty_fetch('xmldump/definition.ihtml');
  gzwrite($defsFile, $def_xml);
  $defResults->MoveNext();
}
gzwrite($defsFile, "</Definitions>\n");
gzclose($defsFile);

// dump lexems and inflected forms table
log_scriptLog("dumping lexems and inflected forms");
$lexemResults = db_execute("SELECT * FROM Lexem");
$lexemFile = gzopen("$FOLDER/$TODAY-lexems.xml.gz", 'wb9');
gzwrite($lexemFile, "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n");
gzwrite($lexemFile, "<Lexems>\n");
while (!$lexemResults->EOF) {
  $lexem = new Lexem();
  $lexem->set($lexemResults->fields);
  smarty_assign('lexem', $lexem);
  smarty_assign('ifs', InflectedForm::loadByLexemId($lexem->id));
  $lexem_xml = smarty_fetch('xmldump/lexem.ihtml');
  gzwrite($lexemFile, $lexem_xml);
  $lexemResults->MoveNext();
}
gzwrite($lexemFile, "</Lexems>\n");
gzclose($lexemFile);

// dump lexem-definition map
log_scriptLog("dumping lexem-definition map");
$ldmResults = db_execute("SELECT M.lexemId, M.definitionId FROM LexemDefinitionMap M, Definition D " .
                         "WHERE D.id = M.definitionId and D.sourceId in (SELECT id FROM Source WHERE canDistribute) AND D.status = 0 " .
                         "ORDER BY M.lexemId, M.definitionId");
$ldmFile = gzopen("$FOLDER/$TODAY-ldm.xml.gz", 'wb9');
gzwrite($ldmFile, "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n");
gzwrite($ldmFile, "<LexemDefinitionMap>\n");
while (!$ldmResults->EOF) {
  gzwrite($ldmFile, sprintf("  <Map lexemId=\"%s\" definitionId=\"%s\"/>\n", $ldmResults->fields[0], $ldmResults->fields[1]));
  $ldmResults->MoveNext();
}
gzwrite($ldmFile, "</LexemDefinitionMap>\n");
gzclose($ldmFile);

/**************************************************************************/

function getActiveUsers() {
  $usersResults = db_execute("SELECT id, nick FROM User WHERE id IN (SELECT DISTINCT userId FROM Definition WHERE status = 0)");
  $users = array();
  while (!$usersResults->EOF) {
    $users[$usersResults->fields[0]] = $usersResults->fields[1];
    $usersResults->MoveNext();
  }
  return $users;
}

function getLastDumpDate($folder) {
  $files = scandir($folder, 1); //descending
  foreach ($files as $file) {
    $matches = array();
    if (preg_match('/^(\\d\\d\\d\\d\\d\\d\\d\\d)-abbrevs.xml.gz$/', $file, $matches)) {
      $candidate = $matches[1];
      if (file_exists("$folder/$candidate-definitions.xml.gz") &&
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

?>

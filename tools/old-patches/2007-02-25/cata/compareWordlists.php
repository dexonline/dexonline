<?php
require_once("../../phplib/util.php");
ini_set('max_execution_time', '3600');
ini_set("memory_limit", "512000000");

print "Running first wordlist query...\n";
$dbResult = mysql_query("select * from wordlist");



print "Building form map...\n";
$formMap = array();
while ($dbRow = mysql_fetch_assoc($dbResult)) {
  $wl = WordList::createFromDbRow($dbRow);
  $formMap[$wl->form] = true;
}

// Hash table of id->description
$inflIdDescrMap = buildInflectionMap();

db_init(pref_getDbHost(), pref_getDbUser(), pref_getDbPassword(),
	'flexonline');

// Hash table of Radu's inflection id -> Cata's inflection id.
$rcInflectionMap = matchInflections($inflIdDescrMap);
print "Running second wordlist query...\n";
$query = 'select wl_form, lexem_forma, wl_analyse ' .
  'from wordlist, lexems ' .
  'where wl_lexem = lexem_id ';
$dbResult = mysql_query($query);

while ($dbRow = mysql_fetch_assoc($dbResult)) {
  list($form, $lexem, $inflId) = buildRWordList($dbRow);
  if (!array_key_exists($form, $formMap)) {
    print "Form: $form (Lexem: $lexem / " .
      $inflIdDescrMap[$rcInflectionMap[$inflId]] . ")\n";
  }
}

/****************************************************************************/

function buildInflectionMap() {
  $m = array();
  $inflections = Inflection::loadAll();
  foreach ($inflections as $i) {
    $m[$i->id] = $i->description;
  }
  return $m;
}

function matchInflections($inflIdDescrMap) {
  $result = array();

  $inflections = Inflection::loadAll();
  foreach ($inflections as $rInfl) {
    foreach ($inflIdDescrMap as $cId => $cDescr) {
      if ($rInfl->description == $cDescr) {
	$result[$rInfl->id] = $cId;
      }
    }
    if (!array_key_exists($rInfl->id, $result)) {
      die("Could not find mapping for {$rInfl->id} ({rInfl->description})\n");
    }
  }

  return $result;
}

function buildRWordList($dbRow) {
  if (!$dbRow) {
    return null;
  }
  return array($dbRow['wl_form'], $dbRow['lexem_forma'], $dbRow['wl_analyse']);
}

?>

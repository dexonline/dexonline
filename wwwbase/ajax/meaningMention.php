<?php
require_once("../../phplib/util.php");

$form = util_getRequestParameter('form');
$qualifier = util_getRequestParameter('qualifier');

// Load the lexems and map them by id
$lexems = Model::factory('Lexem')
  ->table_alias('l')
  ->select('l.*')
  ->distinct()
  ->join('InflectedForm', 'i.lexemId = l.id', 'i')
  ->where('i.formNoAccent', $form)
  ->find_many();

$lexemMap = array();
foreach ($lexems as $l) {
  $lexemMap[$l->id] = $l;
}

// Load the meanings matching the qualifier
$meanings = Model::factory('Meaning')
  ->where_in('lexemId', array_keys($lexemMap))
  ->where_raw("(breadcrumb like '{$qualifier}%' or id like '{$qualifier}%')")
  ->find_many();

// Construct the result set
$results = array();
foreach ($meanings as $m) {
  $l = $lexemMap[$m->lexemId];
  $results[] = array('lexem' => $l->formNoAccent,
                     'meaning' => $m->htmlRep,
                     'meaningId' => $m->id,
                     'breadcrumb' => $m->breadcrumb);
}

print json_encode($results);

?>

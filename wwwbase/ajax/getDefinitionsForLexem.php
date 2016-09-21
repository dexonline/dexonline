<?php
require_once("../../phplib/util.php");

$lexemId = Request::get('lexemId');
$lexem = Lexem::get_by_id($lexemId);
$defs = Definition::loadByEntryId($lexem->entryId);

$results = array();
foreach ($defs as $def) {
  $htmlRep = str_replace("\n", ' ', $def->htmlRep);
  $source = Source::get_by_id($def->sourceId);
  $results[] = array('id' => $def->id,
                     'shortName' => $source->shortName,
                     'status' => $def->getStatusName(),
                     'htmlRep' => $htmlRep);
}

SmartyWrap::assign('results', $results);
SmartyWrap::displayWithoutSkin('ajax/getDefinitionsForLexem.tpl');

?>

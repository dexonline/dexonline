<?php
require_once("../../phplib/util.php");

$lexemId = util_getRequestParameter('lexemId');
$defs = Definition::loadByLexemId($lexemId);

$results = array();
foreach ($defs as $def) {
  $htmlRep = str_replace("\n", ' ', $def->htmlRep);
  $source = Source::get_by_id($def->sourceId);
  $status = $GLOBALS['wordStatuses'][$def->status];
  $results[] = array('id' => $def->id,
                     'shortName' => $source->shortName,
                     'status' => $status,
                     'htmlRep' => $htmlRep);
}

SmartyWrap::assign('results', $results);
SmartyWrap::displayWithoutSkin('ajax/getDefinitionsForLexem.tpl');

?>

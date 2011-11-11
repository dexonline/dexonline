<?php
require_once("../../phplib/util.php");

$lexemId = util_getRequestParameter('lexemId');
$defs = Definition::loadByLexemId($lexemId);

foreach ($defs as $def) {
  $htmlRep = str_replace("\n", ' ', $def->htmlRep);
  $source = Source::get_by_id($def->sourceId);
  $status = $GLOBALS['wordStatuses'][$def->status];
  print "{$def->id}\n{$source->shortName}\n{$status}\n{$htmlRep}\n";
}

?>

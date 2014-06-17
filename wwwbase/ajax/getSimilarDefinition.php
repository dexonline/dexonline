<?php
require_once("../../phplib/util.php");
util_assertModerator(PRIV_EDIT);
util_assertNotMirror();

$definitionId = util_getRequestParameter('definitionId');
$sourceId = util_getRequestParameter('sourceId');
$lexemIds = util_getRequestCsv('lexemIds');

$d = Definition::get_by_id($definitionId);
$d->sourceId = $sourceId;
$similar = $d->loadSimilar($lexemIds);

print $similar ? $similar->htmlRep : '';

?>

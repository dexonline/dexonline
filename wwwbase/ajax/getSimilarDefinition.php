<?php
require_once("../../phplib/util.php");
util_assertModerator(PRIV_EDIT);
util_assertNotMirror();

$sourceId = util_getRequestParameter('s');
$lexemId = util_getRequestParameter('l');

$definition = Definition::loadBySourceAndLexemId($sourceId, $lexemId);

print $definition ? $definition->htmlRep : '';

?>

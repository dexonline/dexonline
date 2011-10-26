<?php

require_once("../../phplib/util.php");

$internalRep = util_getRequestParameter('internalRep');
$sourceId = util_getRequestParameter('sourceId');
$reallyInternalRep = AdminStringUtil::internalizeDefinition($internalRep, $sourceId);
$htmlRep = AdminStringUtil::htmlize($reallyInternalRep, $sourceId);
echo $htmlRep;

?>

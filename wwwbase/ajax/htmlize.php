<?php

require_once("../../phplib/util.php");

$internalRep = Request::get('internalRep');
$sourceId = Request::get('sourceId');
$reallyInternalRep = AdminStringUtil::internalizeDefinition($internalRep, $sourceId);
$htmlRep = AdminStringUtil::htmlize($reallyInternalRep, $sourceId);
echo $htmlRep;

?>

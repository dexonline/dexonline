<?php

require_once("../../phplib/Core.php");

$internalRep = Request::get('internalRep');
$sourceId = Request::get('sourceId');
$reallyInternalRep = StringUtil::sanitize($internalRep, $sourceId);
$htmlRep = StringUtil::htmlize($reallyInternalRep, $sourceId);
echo $htmlRep;

?>

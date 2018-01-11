<?php

require_once("../../phplib/Core.php");

$internalRep = Request::get('internalRep');
$sourceId = Request::get('sourceId');
$reallyInternalRep = Str::sanitize($internalRep, $sourceId);
$htmlRep = Str::htmlize($reallyInternalRep, $sourceId);
echo $htmlRep;

?>

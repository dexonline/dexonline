<?php

require_once("../../phplib/Core.php");

$internalRep = Request::get('internalRep');
$sourceId = Request::get('sourceId');
$reallyInternalRep = AdminStringUtil::sanitize($internalRep, $sourceId);
$htmlRep = AdminStringUtil::htmlize($reallyInternalRep, $sourceId);
echo $htmlRep;

?>

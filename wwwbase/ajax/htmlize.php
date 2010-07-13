<?

require_once("../../phplib/util.php");

$internalRep = util_getRequestParameter('internalRep');
$sourceId = util_getRequestParameter('sourceId');
$reallyInternalRep = text_internalizeDefinition($internalRep, $sourceId);
$htmlRep = text_htmlize($reallyInternalRep, $sourceId);
echo $htmlRep;

?>

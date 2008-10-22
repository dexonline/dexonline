<?

require_once("../../phplib/util.php");

$internalRep = util_getRequestParameter('internalRep');
$reallyInternalRep = text_internalizeDefinition($internalRep);
$htmlRep = text_htmlize($reallyInternalRep);
echo $htmlRep;

?>

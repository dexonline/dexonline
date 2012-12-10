<?php

require_once("../../phplib/util.php"); 
util_assertModerator(PRIV_EDIT);

$id = util_getRequestIntParameter('id');
$def = Definition::get_by_id($id);
$source = Source::get_by_id($def->sourceId);

$tree = Structure::parse($def);
foreach ($tree['meanings'] as $i => $m) {
  $tree['meanings'][$i]['html'] = AdminStringUtil::htmlize($m['text'], $source->id);
}
$numRecords = count($tree['meanings']);

//header('Content-Type: text/xml; charset=UTF-8');
echo "<?xml version='1.0' encoding='utf-8'?>\n";
echo "<rows>";
echo "<page>1</page>";
echo "<total>1</total>";
echo "<records>$numRecords</records>";
foreach ($tree['meanings'] as $k => $m) {
	echo "<row id=\"{$k}\">";			
	echo "<cell><![CDATA[". $m['hierarchy'] . "]]></cell>";
	echo "<cell><![CDATA[". $m['html'] . "]]></cell>";
	echo "<cell><![CDATA[". $m['text'] . "]]></cell>";
	echo "</row>";
}
echo "</rows>";

?>

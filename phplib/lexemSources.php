<?php
//It should be in sync with table Lexem, field source
$GLOBALS['lexemSourcesArray'] = array(
	"doom"	=> array( "name" => "DOOM" ), 
	"dex" 	=> array( "name" => "DEX" ), 
	"dmlr"	=> array( "name" => "DMLR" ), 
	"nodex"	=> array( "name" => "NODEX" ),
	"mda"	=> array( "name" => "MDA" ),
	"der"	=> array( "name" => "DER" ),
	"orto"	=> array( "name" => "Ortografic" ),
	"dlrm"	=> array( "name" => "DLRM '58" ),
);

function getNamesOfSources($sourceList) {
	if(!$sourceList) return "";
	$sList = explode(",", $sourceList);
	$names = array();
	foreach($sList as $source) {
		if (is_array($GLOBALS['lexemSourcesArray'][$source])){
			$names[] = $GLOBALS['lexemSourcesArray'][$source]["name"];
		}
	}
	return implode(", ", $names);
}

function getSourceArrayChecked($sourceList) {
	$sList = explode(",", $sourceList);
	$sourceArray = array();
	foreach($GLOBALS['lexemSourcesArray'] as $id => $source) {
		$sourceArray[$id] = array("name" => $source["name"]);
		if (in_array($id, $sList) ) {
			$sourceArray[$id]["checked"] = 1;
		}
	}
	return $sourceArray;
}

?>

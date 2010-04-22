<?
//It should be in sync with table Lexem, field source
$GLOBALS['lexemSourcesArray'] = array(
	"doom2" => array( "name" => "DOOM 2" ), 
	"dex98" => array( "name" => "DEX '98" ), 
	"dmlr" 	=> array( "name" => "DMLR" ), 
	"doom" 	=> array( "name" => "DOOM" ), 
	"dex09" => array( "name" => "DEX '09" )
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

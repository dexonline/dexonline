<?php

class LexemSources {
  //It should be in sync with table Lexem, field source
  private static $lexemSourcesArray = array(
                                            "doom"	=> array( "name" => "DOOM" ), 
                                            "dex" 	=> array( "name" => "DEX" ), 
                                            "dmlr"	=> array( "name" => "DMLR" ), 
                                            "nodex"	=> array( "name" => "NODEX" ),
                                            "mda"	=> array( "name" => "MDA" ),
                                            "der"	=> array( "name" => "DER" ),
                                            "orto"	=> array( "name" => "Ortografic" ),
                                            "dlrm"	=> array( "name" => "DLRM '58" ),
                                            );

  static function getNamesOfSources($sourceList) {
    if(!$sourceList) return "";
    $sList = explode(",", $sourceList);
    $names = array();
    foreach($sList as $source) {
      if (is_array(self::$lexemSourcesArray[$source])){
        $names[] = self::$lexemSourcesArray[$source]["name"];
      }
    }
    return implode(", ", $names);
  }

  static function getSourceArrayChecked($sourceList) {
    $sList = explode(",", $sourceList);
    $sourceArray = array();
    foreach(self::$lexemSourcesArray as $id => $source) {
      $sourceArray[$id] = array("name" => $source["name"]);
      if (in_array($id, $sList) ) {
        $sourceArray[$id]["checked"] = 1;
      }
    }
    return $sourceArray;
  }
}

?>

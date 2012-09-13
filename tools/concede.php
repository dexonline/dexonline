<?php

/**
 * Script created by Cristian Chilipirea to import the database for moara-cuvintelor.
 **/

require_once __DIR__ . '/../phplib/util.php';

setlocale(LC_ALL, "ro_RO.utf8");
$str = file_get_contents("/tmp/a.xml");
//$str = html_entity_decode($str);

$toreplace = array (
                    "&abreve;", "&acirc;", "&icirc;", "&scedil;", "&tcedil;", "&Abreve;", 
                    "&Acirc;", "&Icirc;", "&Scedil;", "&Tcedil;", "&aacute;", "&egrave;", 
                    "&eacute;","&ubreve;", "&ouml;", "&agrave;", "&oacute;", "&ocirc;",
                    "&deg;", "&euml;", "&ccedil;", "&gbreve;", "&iacute;","&iuml;",
                    "&ecaron;", "&uuml;", "&igrave;", "&atilde;", "&ecirc;", "&imacr;", 
                    "&ograve;","&ibreve;");

$replace = array (
                  "ă", "â", "î", "ş", "ţ", "Ă", 
                  "Â", "Î", "Ş", "Ţ", "á", "è", 
                  "é", "ŭ", "ö", "à", "ó", "ô",
                  "°", "ë", "ç", "ğ", "í", "ï",
                  "ě", "ü", "ì", "ã", "ê", "ī", 
                  "ò", "ĭ");

$str = str_replace($toreplace, $replace, $str);

libxml_use_internal_errors(true);
$xml = simplexml_load_string($str);
if (!$xml) {
  die("Failed to load XML\n");
}

foreach ($xml->children() as $child) {
  foreach ($child->children() as $childa) {
    $count=0;
    foreach ($childa->children() as $childb) {
        
      if ($childb->getName() == "hw") {
        echo "<b>".strtolower($childb)."</b>";
        $data = Model::factory('Definition')->where_equal('lexicon', strtolower($childb))->find_one();
        if ($data) {
          echo ":" . $data->id . ":";
          $definitionId = $data->id;
        } else {
          break;
        }
      }
      if ($childb->getName() == "struc") {
        foreach ($childb->children() as $childc)
          if ($childc->getName() == "def") {
            echo ":" . $childc;
            $toInsert = Model::factory('DefinitionSimple')->create();
            $toInsert->definitionId = $definitionId;
            $toInsert->definition = trim($childc);
            $toInsert->createDate = time();
            $toInsert->modDate = time();
            $toInsert->save();
          }
      }
    }
    echo "\n";
  }
}
?> 

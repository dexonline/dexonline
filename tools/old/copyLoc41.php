<?php

/**
 * Copy LOC info from 4.1 back to 5.0, then include DLRM lexems from the list that Matei provided.
 */

require_once("../phplib/util.php");
define('FILENAME', '/tmp/DLRM-Dan.csv');
define('CORRECTED_FILENAME', '/tmp/corectat.csv');

// First build a hashmap of 4.1 lexems.
$lexem41Map = [];
$dbResult = db_execute("select id, form, isLoc, concat(modelType, modelNumber, restriction) from LOC_4_1.Lexem");
while (!$dbResult->EOF) {
  $lexem41Map[$dbResult->fields[0]] = array($dbResult->fields[1], intval($dbResult->fields[2]), $dbResult->fields[3]);
  $dbResult->MoveNext();
}

// Next, load the corrected file (Matei provided this as an errata to Dan's file)
$lexemDlrmMap = [];
$f = fopen(CORRECTED_FILENAME, 'r');
while (($fields = fgetcsv($f)) !== false) {
  $formNoAccent = locNotationToDexNotation($fields[0]);
  $model = $fields[1] ? $fields[1] : 'I1';
  $lexem = Lexem::get("formNoAccent = '{$formNoAccent}' and concat(modelType, modelNumber, restriction) = '$model'");
  if ($lexem) {
    $lexemDlrmMap[$lexem->id] = true;
  }
}
fclose($f);

// Next, load the file and build a hashmap of DLRM lexems to keep in LOC
$f = fopen(FILENAME, 'r');
while (($fields = fgetcsv($f)) !== false) {
  $formNoAccent = locNotationToDexNotation($fields[1]);
  $models = preg_split('/\s+/', trim($fields[2]));
  $modelStrings = '';
  foreach($models as $m) {
    if ($m) {
      if ($modelStrings) {
        $modelStrings .= ',';
      }
      $modelStrings .= "\"$m\"";
    }
  }
  if (!$modelStrings) {
    $modelStrings = "\"I1\"";
  }
  // print "$modelStrings\n";
  $lexems = db_find(new Lexem(), "formNoAccent = '{$formNoAccent}' and concat(modelType, modelNumber, restriction) in ($modelStrings)");
  if (!count($lexems)) {
    $lexems = db_find(new Lexem(), "formNoAccent = '{$formNoAccent}'");
    if (count($lexems)) {
      $newModels = "";
      foreach ($lexems as $l) {
        $newModels .= "{$l->modelType}{$l->modelNumber}{$l->restriction} ";
      }
      print "Lexem: {$formNoAccent} Model(e) Dan: {$fields[2]}, Model(e) DEX online {$newModels}\n";
    } else {
      // print "Lexem negăsit: $formNoAccent {$fields[2]}\n";
    }
  }
  foreach ($lexems as $l) {
    $lexemDlrmMap[$l->id] = true;
  }
}
fclose($f);

// Next, go through all the lexems. Those in $lexemDlrmMap and those that were in LOC 4.1 will remain in LOC, the rest will be excluded.
$dbResult = db_execute("select * from Lexem");
while (!$dbResult->EOF) {
  $l = new Lexem();
  $l->set($dbResult->fields);
  $newValue = array_key_exists($l->id, $lexemDlrmMap) || (array_key_exists($l->id, $lexem41Map) && $lexem41Map[$l->id][1]);
  if ($newValue != $l->isLoc) {
    $lexemDetails = "{$l->id} {$l->formNoAccent} {$l->modelType}{$l->modelNumber}{$l->restriction}";
    if ($newValue) {
      print "Includ {$lexemDetails} în LOC\n";
    } else {
      print "Exclud {$lexemDetails} din LOC\n";
    }
    $l->isLoc = $newValue;
    $l->save();
  }
  $dbResult->MoveNext();
}


/*************************************************************************/

function locNotationToDexNotation($s) {
  $s = str_replace(array('ş', 'Ş', 'ţ', 'Ţ'), array('ș', 'Ș', 'ț', 'Ț'), $s);
  $s = mb_strtolower($s);
  return $s;
}

?>

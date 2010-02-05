<?
// After migrating the entire schema to UTF8, some definitions were
// converted badly.
require_once("../../phplib/util.php");

$data = file('data.txt');
$i = 0;
$numLines = count($data);

foreach ($data as $line) {
  $i++;
  $components = split('\|\|\|\|\|', $line);
  $id = $components[0];
  $internalRep = $components[1];
  $internalRep = str_replace(chr(0x96), '-', $internalRep);
  $internalRep = str_replace('â' . chr(0x80) . chr(0x93), '-', $internalRep);  
  $internalRep = str_replace('\\', '', $internalRep);
  $internalRep = trim($internalRep);
  print "Patching definition $i/$numLines ($id)\n";
  $def = Definition::load($id);
  $def->internalRep = $internalRep;
  $def->htmlRep = text_htmlize($internalRep);
  $def->lexicon = text_extractLexicon($def);
  $def->save();
}

?>

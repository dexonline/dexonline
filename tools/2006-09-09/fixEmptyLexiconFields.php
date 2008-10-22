<?

require_once("../../phplib/util.php");

$query = "select Id from Definition where Lexicon = '' and Status = 0";
$dbResult = mysql_query($query);

while ($row = mysql_fetch_assoc($dbResult)) {
  $def = Definition::load($row['Id']);
  $words = Word::loadByDefinitionId($def->id);
  $dnames = Word::joinCommaSeparatedDnames($words);
  $def->lexicon = text_dnameToLexicon($words[0]->dname);
  print "Fixing definition " . $def->id . " / $dnames; new Lexicon is " .
    $def->lexicon . "\n";
  $def->save();
}

?>

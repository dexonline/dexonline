<?

require_once("../../phplib/util.php");

$query = "select Id from Definition where Lexicon rlike '[^a-eA-Z]'";
$dbResult = mysql_query($query);

while ($row = mysql_fetch_assoc($dbResult)) {
  $def = Definition::load($row['Id']);
  $words = Word::loadByDefinitionId($def->id);
  foreach ($words as $word) {
    $word->dname = text_internalizeDname($word->dname);
    $word->name = text_wordNameToLatin($word->dname);
    $word->save();
  }

  $dnames = Word::joinCommaSeparatedDnames($words);
  if (count($words)) {
    $def->lexicon = text_dnameToLexicon($words[0]->dname);
  } else {
    print "*********** WARNING: No words for this definition: *******\n";
    $def->lexicon = '';
  }
  print "Fixing definition " . $def->id . " / $dnames; new Lexicon is " .
    $def->lexicon . "\n";
  $def->save();
}

?>

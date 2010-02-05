<?

require_once("../../phplib/util.php");

$lines = file('/tmp/rez_bio.txt');
foreach($lines as $line) {
  $components = split('---', $line);
  if (count($components) != 3) {
    echo "Bad line: $line";
    continue;
  }
  $id = trim($components[0]);
  $name = trim(strtolower($components[1]));
  $latinList = trim(strtolower($components[2]));
  if (!is_numeric($id)) {
    echo "Bad id: $id\n";
    continue;
  }

  $definition = Definition::load($id);
  $words = Word::loadByDefinitionId($id);

  $nameExists = false;
  foreach ($words as $word) {
    if (strtolower($word->name) == $name) {
      $nameExists = true;
    }
  }
  if (!$nameExists) {
    echo "Name does not match id: $name, $id";
    continue;
  }

  $dnames = Word::joinCommaSeparatedDnames($words);
  echo "Adding words for $id ($dnames)\n";

  $latinNames = split('\|', $latinList);
  foreach ($latinNames as $latinName) {
    $latinName = trim($latinName);

    // Split into words. If multiple words, add each individual word AND the
    // name as a whole.
    $latinWords = split(' ', $latinName);
    $numWords = count($latinWords);
    for ($i = 0; $i < $numWords; $i++) {
      $latinWords[$i] = trim($latinWords[$i]);
    }
    if ($numWords > 1) {
      $latinWords[] = implode('', $latinWords);
    }
    foreach ($latinWords as $latinWord) {
      if (hasDname($words, $latinWord)) {
        echo "  ********** Already contains $latinWord\n";
      } else {
        $word = new Word();
        $word->dname = text_internalizeDname($latinWord);
        $word->name = text_wordNameToLatin($word->dname);
        $word->priority = count($words);
        $word->definitionId = $id;
        $words[] = $word;
        $word->save();
        echo "  Added $latinWord with priority " . $word->priority . "\n";
      }
    }
  }
}

function hasDname($words, $dname) {
  foreach($words as $word) {
    if ($word->dname == $dname) {
      return true;
    }
  }
  return false;
}


?>

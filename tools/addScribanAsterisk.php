<?php

require_once __DIR__ . '/../phplib/util.php';

$lines = file('/tmp/Scriban.txt');
$scribanSrc = Source::get_by_shortName('scriban');

foreach ($lines as $line) {
  $line = mb_strtolower(trim($line));
  if ($line) {
    $symbol = $line[0];
    $line = str_replace(array('ş', 'ţ', '*', '!'), array('ș', 'ț', '', ''), $line);
    $query = Model::factory('Definition')->where('sourceId', $scribanSrc->id)->where('status', 0);
    if (ctype_digit($line)) {
      $query = $query->where('id', $line);
    } else {
      $query = $query->where('lexicon', $line);
    }
    $defs = $query->find_many();
    if (count($defs) != 1) {
      printf("Am găsit %d definiții pentru [%s]\n", count($defs), $line);
    } else if (strpos(substr($defs[0]->internalRep, 0, 10), $symbol) !== false) {
      printf("Definiția pentru [%s] conține deja simbolul [%s]\n", $line, $symbol);
    } else if ($defs[0]->internalRep[0] != '@') {
      printf("Definiția pentru [%s] nu începe cu [@]\n", $line);
    } else {
      $defs[0]->internalRep = '@' . $symbol . substr($defs[0]->internalRep, 1);
      $defs[0]->htmlRep = AdminStringUtil::htmlize($defs[0]->internalRep, $scribanSrc->id, $errors);
      $defs[0]->save();
    }
  }
}

?>

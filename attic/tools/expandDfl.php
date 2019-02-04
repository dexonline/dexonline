<?php
/**
 * Replace genus abbreviations in DFL with full names, so that the lexicon is properly extracted.
 **/

require_once __DIR__ . '/../lib/Core.php';

define('SOURCE_ID', 63);

$defs = Model::factory('Definition')
      ->where('sourceId', SOURCE_ID)
      ->where('status', Definition::ST_ACTIVE)
      ->order_by_asc('id')
      ->find_many();

$recentGenera = [];

foreach ($defs as $d) {
  if (preg_match('/^@([A-ZÉË]+)@ /', $d->internalRep, $matches)) {
    // Genus
    $genus = mb_strtolower($matches[1]);
    $species = null;
    printf("%s gen %s\n", $matches[0], $genus);
  } else if (preg_match('/^@([A-Z][a-zéë]+) (x |var\. )?([-a-zë]+)@/', $d->internalRep, $matches)) {
    // Genus species
    $genus = mb_strtolower($matches[1]);
    $species = $matches[3];
    printf("%s gen %s specie %s\n", $matches[0], $genus, $species);
  } else if (preg_match('/^@(([A-Z])\.) (x |var\. )?([-a-zë]+)@/', $d->internalRep, $matches)) {
    // G. species
    $genus = mb_strtolower($matches[2]);
    $species = $matches[4];
    printf("%s gen %s specie %s\n", $matches[0], $genus, $species);
  } else {
    $genus = null;
    $species = null;
    printf("Nu pot deduce numele speciei: [%s] %s%d\n",
           mb_substr($d->internalRep, 0, 80),
           "https://dexonline.ro/admin/definitionEdit.php?definitionId=",
           $d->id);
  }

  if ($genus) {
    if (strlen($genus) > 1) {
      // explicit genus name: add it to $recentGenera
      if (!in_array($genus, $recentGenera)) {
        $recentGenera[] = $genus;
        if (count($recentGenera) > 3) {
          array_shift($recentGenera);
        }
      }
    } else {
      // initial: find an associated lexeme that's also in $recentGenera
      $l = getGenusLexeme($d, $recentGenera, $genus);
      if ($l) {
        // confirmed, plug it in the definition
        $newRep = '@' . ucfirst($l->formNoAccent) . substr($d->internalRep, 3);
        printf("[%s] -> [%s]\n", mb_substr($d->internalRep, 0, 80), mb_substr($newRep, 0, 80));
        $d->internalRep = $newRep;
        $d->htmlRep = AdminStringUtil::htmlize($d->internalRep, SOURCE_ID);
        $d->extractLexicon();
        $d->save();
      } else {
        printf("Genul nu apare recent: [%s] %s%s\n",
               mb_substr($d->internalRep, 0, 80),
               "https://dexonline.ro/admin/definitionEdit.php?definitionId=",
               $d->id);
      }
    }
  }
}

/*************************************************************************/

// finds an associated lexeme (or fragment) that's also in $recentGenera and starts
// with the given initial
function getGenusLexeme($d, $recentGenera, $initial) {
  $l = Model::factory('Lexem')
     ->table_alias('l')
     ->select('l.*')
     ->join('EntryLexem', [ 'l.id', '=', 'el.lexemId'], 'el')
     ->join('EntryDefinition', [ 'el.entryId', '=', 'ed.entryId'], 'ed')
     ->where('ed.definitionId', $d->id)
     ->where_in('l.formNoAccent', $recentGenera)
     ->where_like('l.formNoAccent', "{$initial}%")
     ->find_one();

  if (!$l) {
    $l = Model::factory('Lexem')
       ->table_alias('l')
       ->select('l.*')
       ->join('Fragment', [ 'l.id', '=', 'f.partId'], 'f')
       ->join('Lexem', [ 'f.lexemId', '=', 'c.id'], 'c') // compound lexeme
       ->join('EntryLexem', [ 'c.id', '=', 'el.lexemId'], 'el')
       ->join('EntryDefinition', [ 'el.entryId', '=', 'ed.entryId'], 'ed')
       ->where('ed.definitionId', $d->id)
       ->where_in('l.formNoAccent', $recentGenera)
       ->where_like('l.formNoAccent', "{$initial}%")
       ->find_one();
  }

  return $l;
}

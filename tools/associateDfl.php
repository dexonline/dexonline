<?php

/**
 * Create proper lexemes and entries for genera and species.
 **/

require_once __DIR__ . '/../phplib/Core.php';

define('SOURCE_ID', 63);
define('START_AT', '');

$defs = Model::factory('Definition')
      ->where('sourceId', SOURCE_ID)
      ->where('status', Definition::ST_ACTIVE)
      ->where_gte('lexicon', START_AT)
      ->order_by_asc('lexicon')
      ->find_many();

foreach ($defs as $d) {
  
  if (preg_match('/^@([A-ZÉË]+)@ /', $d->internalRep, $matches)) {
    // Genus
    $genus = ucfirst(mb_strtolower($matches[1]));
    fixGenus($d, $genus);
  } else if (preg_match('/^@([A-Z][a-zéë]+) (x |var\. )?([-a-zë]+)@/', $d->internalRep, $matches)) {
    // Genus species
    $genus = $matches[1];
    $species = $matches[3];
    // printf("%s gen %s specie %s\n", $matches[0], $genus, $species);
  } else {
    $genus = null;
    $species = null;
    printf("Nu pot deduce numele speciei: [%s] %s\n",
           mb_substr($d->internalRep, 0, 80),
           defUrl($d));
  }

}

/*************************************************************************/

function defUrl($d) {
  return "https://dexonline.ro/admin/definitionEdit.php?definitionId={$d->id}";
}

function entryUrl($e) {
  return "https://dexonline.ro/editEntry.php?id={$e->id}";
}

function choice($prompt, $choices) {
  do {
    $choice = readline($prompt);
  } while (!in_array($choice, $choices));
  return $choice;
}

// counts the definitions not in DFL associated with $e
function countOutsideDefs($e) {
  return Model::factory('Definition')
    ->table_alias('d')
    ->join('EntryDefinition', ['d.id', '=', 'ed.definitionId'], 'ed')
    ->where_in('d.status', [Definition::ST_ACTIVE, Definition::ST_HIDDEN])
    ->where_not_equal('d.sourceId', SOURCE_ID)
    ->where('ed.entryId', $e->id)
    ->count();
}

// offers to delete all of $e's lexemes and merge it into $main
function offerMerge($e, $main) {
  printf("Intrarea %s %s nu are definiții din afara DFL.\n", $e, entryUrl($e));
  print("Lexeme:");
  foreach ($e->getLexems() as $l) {
    print(" {$l} {$l->modelType}{$l->modelNumber}");
  }
  print("\n");

  $c = choice('Șterg lexemele și unific intrarea? ', ['d', 'n']);
  if ($c == 'd') {
    foreach ($e->getLexems() as $l) {
      $l->delete();
    }
    $e->mergeInto($main->id);
  }
}

function fixGenus($d, $genus) {
  print "\n**** Verific intrările pentru genul {$genus}\n\n";

  // find the entry for the genus
  $genusEntries = Model::factory('Entry')
                ->tableAlias('e')
                ->select('e.*')
                ->join('EntryDefinition', ['e.id', '=', 'ed.entryId'], 'ed')
                ->where('ed.definitionId', $d->id)
                ->where_in('e.description', [$genus, "{$genus} (gen de plante)"])
                ->find_many();
  if (count($genusEntries) != 1) {
    printf("Genul nu are exact o intrare: [%s] %s\n",
           mb_substr($d->internalRep, 0, 80),
           defUrl($d));
    exit;
  }
  $main = $genusEntries[0];
  printf("Intrarea genului: %s <%s>\n", $main, entryUrl($main));

  // offer to merge other entries into $main under certain conditions
  foreach ($d->getEntries() as $e) {
    if ($e->id != $main->id) {
      $outsideDefs = countOutsideDefs($e);
      if (!$outsideDefs) {
        offerMerge($e, $main);
      }
    }
  }
}

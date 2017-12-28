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
    $choice = readline($prompt . ' ');
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

// counts the entries of $l except for $e
function countOtherEntries($l, $e) {
  return Model::factory('EntryLexem')
    ->where('lexemId', $l->id)
    ->where_not_equal('entryId', $e->id)
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

  $c = choice('Șterg lexemele și unific intrarea?', ['d', 'n']);
  if ($c == 'd') {
    foreach ($e->getLexems() as $l) {
      $l->delete();
    }
    $e->mergeInto($main->id);
  }
}

// offers to delete lexemes in this entry, except for the entry's main lexeme
function offerDeleteLexemes($entry, $mainLexeme) {
  foreach ($entry->getLexems() as $l) {
    if (($l->id != $mainLexeme->id) && !$l->isLoc) {
      $otherEntries = countOtherEntries($l, $entry);
      $prompt = sprintf("Șterg lexemul %s (%s%s), asociat cu alte %s intrări?",
                        $l, $l->modelType, $l->modelNumber, $otherEntries);
      $c = choice($prompt, ['d', 'n']);
      if ($c == 'd') {
        $l->delete();
      }
    }
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
  /* foreach ($d->getEntries() as $e) { */
  /*   if ($e->id != $main->id) { */
  /*     $outsideDefs = countOutsideDefs($e); */
  /*     if (!$outsideDefs) { */
  /*       offerMerge($e, $main); */
  /*     } */
  /*   } */
  /* } */

  // capitalize the entry and add the "(gen de plante)" description
  $desc = "{$genus} (gen de plante)";
  if ($desc != $main->description) {
    printf("Redenumesc intrarea în [{$desc}]\n");
    $main->description = $desc;
    $main->save();
  }

  // get the matching lexem
  $genusLexemes = Model::factory('Lexem')
    ->tableAlias('l')
    ->select('l.*')
    ->join('EntryLexem', ['l.id', '=', 'el.lexemId'], 'el')
    ->where('el.entryId', $main->id)
    ->where('l.formNoAccent', $genus)
    ->where_in('l.modelType', ['T', 'I'])
    ->find_many();
  if (count($genusLexemes) > 1) {
    printf("Genul are mai multe lexeme posibile.\n");
    exit;
  }

  if (empty($genusLexemes)) {
    $l = Lexem::create($genus, 'I', '2.1');
    printf("Creez lexemul %s (%s%s)\n", $l, $l->modelType, $l->modelNumber);
  } else {
    $l = $genusLexemes[0];
    $l->setForm($genus);
    $l->modelType = 'I';
    $l->modelNumber = '2.1';
  }

  $l->description = 'gen de plante';
  $l->noAccent = true;
  $l->deepSave();
  EntryLexem::associate($main->id, $l->id);

  // offer to delete other lexemes
  // offerDeleteLexemes($main, $lmain);
}

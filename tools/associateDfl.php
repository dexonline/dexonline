<?php
/**
 * Create proper entries and compound lexemes for species.
 **/

require_once __DIR__ . '/../phplib/Core.php';

define('SOURCE_ID', 63);

$defs = Model::factory('Definition')
      ->where('sourceId', SOURCE_ID)
      ->where('status', Definition::ST_ACTIVE)
      ->order_by_asc('lexicon')
      ->find_many();

foreach ($defs as $d) {
  
  if (preg_match('/^@([A-Z][a-zéë]+) (x )?([-a-zë]+)@/', $d->internalRep, $matches)) {
    // Genus species
    $genus = $matches[1];
    $species = $matches[3];
    printf("%s gen %s specie %s\n", $matches[0], $genus, $species);

    // load the genus lexeme
    $glexeme = Lexem::get_by_formNoAccent_modelType_modelNumber($genus, 'I', '2.1');
    $glexeme or die("Nu găsesc lexemul pentru gen.\n");

    // look for a species lexeme
    $slexemes = Model::factory('Lexem')
              ->where_raw('(binary formNoAccent like ?)', [$species])
              ->where_in('modelType', ['T', 'I'])
              ->find_many();
    if (count($slexemes) > 1) {
      printf("%d lexeme %s\n", count($slexemes), defUrl($d));
    }
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

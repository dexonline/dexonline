<?php
/**
 * Create proper entries and compound lexemes for species.
 **/

require_once __DIR__ . '/../phplib/Core.php';

define('SOURCE_ID', 63);
define('START_AT', 'spiraea albiflora');

$defs = Model::factory('Definition')
      ->where('sourceId', SOURCE_ID)
      ->where('status', Definition::ST_ACTIVE)
      ->where_gte('lexicon', START_AT)
      ->order_by_asc('lexicon')
      ->find_many();

foreach ($defs as $d) {
  
  if (preg_match('/^@(([A-Z][a-zéë]+) (x )?([-a-zë]+))@/', $d->internalRep, $matches)) {
    // Genus species
    $entryName = $matches[1];
    $genus = $matches[2];
    $species = $matches[4];
    $hybrid = ($matches[3] != '');
    printf("%s gen %s specie %s %s\n",
           $entryName, $genus, $species, $hybrid ? 'hibrid' : '');

    // load or create the entry and associate it with the definition
    $e = Entry::get_by_description($entryName);
    if (!$e) {
      printf("Creez o intrare pentru %s\n", $entryName);
      $e = Entry::createAndSave($entryName);
    }
    EntryDefinition::associate($e->id, $d->id);

    // load the genus lexeme
    $glexeme = Lexem::get_by_formNoAccent_modelType_modelNumber($genus, 'I', '2.1');
    $glexeme or die("Nu găsesc lexemul pentru gen.\n");

    // load and update the species lexeme, or create it if it doesn't exist
    $slexeme = getSpeciesLexeme($species);
    EntryLexem::dissociate($e->id, $slexeme->id);

    // look for a compound lexeme
    $clexeme = Lexem::get_by_form_compound($entryName, true);
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

function getSpeciesLexeme($species) {
  $l = Model::factory('Lexem')
    ->where_raw('(binary form like ?)', [$species])
    ->where_any_is([
      [ 'modelType' => 'T', 'modelNumber' => '1'],
      [ 'modelType' => 'I', 'modelNumber' => '1'],
      [ 'modelType' => 'I', 'modelNumber' => '2'],
      [ 'modelType' => 'I', 'modelNumber' => '2.2'],
    ])
    ->find_one();
  if ($l) {
    $l->modelType = 'I';
    $l->modelNumber = '2.2';
  } else {
    printf("Creez un lexem pentru %s\n", $species);
    $l = Lexem::create($species, 'I', '2.2');
  }
  $l->description = 'specie de plante';
  $l->noAccent = true;
  $l->deepSave();
  return $l;
}

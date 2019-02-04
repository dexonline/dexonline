<?php
/**
 * Create proper entries and compound lexemes for species.
 **/

require_once __DIR__ . '/../lib/Core.php';

define('SOURCE_ID', 63);
define('START_AT', '');

$xmarker = Lexem::get_by_form_modelType_modelNumber('x', 'I', '2');
$xmarker or die("Nu găsesc lexemul x (I2).\n");

$tag = Tag::get_by_value('nomenclatura binară');
$tag or die("Nu găsesc eticheta [nomenclatura binară].\n");

$defs = Model::factory('Definition')
      ->where('sourceId', SOURCE_ID)
      ->where('status', Definition::ST_ACTIVE)
      ->where_gte('lexicon', START_AT)
      ->order_by_asc('lexicon')
      ->find_many();

foreach ($defs as $d) {
  
  if (preg_match('/^@(([A-Z][a-zéë]+) (x )?([-a-zë]+))@/', $d->internalRep, $matches)) {
    // Identify elements. No hyphens.
    $entryName = str_replace('-', '', $matches[1]);
    $genus = $matches[2];
    $species = str_replace('-', '', $matches[4]);
    $hybrid = ($matches[3] != '');
    printf("**** %s gen %s specie %s %s\n",
           $entryName, $genus, $species, $hybrid ? 'hibrid' : '');

    // load or create the entry and associate it with the definition
    $e = Entry::get_by_description($entryName);
    if (!$e) {
      printf("Creez o intrare pentru %s\n", $entryName);
      $e = Entry::createAndSave($entryName);
    }
    EntryDefinition::associate($e->id, $d->id);
    dissociateGenusEntries($d);

    // load the genus lexeme
    $glexeme = Lexem::get_by_formNoAccent_modelType_modelNumber($genus, 'I', '2.1');
    $glexeme or die("Nu găsesc lexemul pentru gen.\n");

    // load and update the species lexeme, or create it if it doesn't exist
    $slexeme = getSpeciesLexeme($species);
    EntryLexem::dissociate($e->id, $slexeme->id);

    // load the compound lexeme or create it if it doesn't exist
    $clexeme = Lexem::get_by_form_compound($entryName, true);
    if (!$clexeme) {
      $clexeme = makeCompoundLexeme($entryName, $glexeme, $slexeme, $hybrid, $xmarker);
    }
    EntryLexem::associate($e->id, $clexeme->id);
    ObjectTag::associate(ObjectTag::TYPE_LEXEM, $clexeme->id, $tag->id);
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

function makeCompoundLexeme($form, $glexeme, $slexeme, $hybrid, $xmarker) {
  printf("Creez lexemul compus %s\n", $form);
  $l = Lexem::create($form, 'I', '0');
  $l->compound = true;
  $l->noAccent = true;

  $rank = 0;
  $fragments = [];
  $fragments[] = Fragment::create($glexeme->id, Fragment::DEC_INVARIABLE, true, $rank++);
  if ($hybrid) {
    $fragments[] = Fragment::create($xmarker->id, Fragment::DEC_INVARIABLE, false, $rank++);
  }
  $fragments[] = Fragment::create($slexeme->id, Fragment::DEC_INVARIABLE, false, $rank++);
  $l->setFragments($fragments);

  $l->deepSave();
  return $l;
}

function dissociateGenusEntries($d) {
  foreach ($d->getEntries() as $e) {
    if (StringUtil::endsWith($e->description, '(gen de plante)')) {
      printf("Disociez intrarea [%s] de definiția [%s]\n", $e->description, $d->lexicon);
      EntryDefinition::dissociate($e->id, $d->id);
    }
  }
}

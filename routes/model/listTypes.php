<?php
User::mustHave(User::PRIV_EDIT);

$showAddForm = Request::get('add');
$editId = Request::get('editId');
$deleteId = Request::get('deleteId');
$saveButton = Request::has('saveButton');
$id = Request::get('id');
$code = Request::get('code');
$canonical = Request::get('canonical');
$description = Request::get('description');

if ($showAddForm) {
  Smart::assign('addModelType', Model::factory('ModelType')->create());
}

if ($saveButton && !$id) {
  // Add a new model
  $mt = Model::factory('ModelType')->create();
  $mt->code = mb_strtoupper($code);
  $mt->canonical = $canonical;
  $mt->description = $description;
  if (validateAdd($mt)) {
    $mt->save();
    Log::notice("Created model type {$mt->code} ({$mt->description})");
    FlashMessage::add("Am adăugat tipul de model '{$mt->code}'.", 'success');
    Util::redirectToSelf();
  } else {
    $showAddForm = true;
    Smart::assign('addModelType', $mt);
  }
}

if ($saveButton && $id) {
  // Save an existing model
  $mt = ModelType::get_by_id($id);
  $mt->description = $description;
  if (validateEdit($mt)) {
    $mt->save();
    Log::notice("Changed description for model type {$mt->code} ({$mt->description})");
    FlashMessage::add('Am salvat descrierea.', 'success');
    Util::redirectToSelf();
  } else {
    Smart::assign('editModelType', $mt);
  }
}

if ($editId) {
  // Load model type to be edited
  Smart::assign('editModelType', ModelType::get_by_id($editId));
}

if ($deleteId) {
  $mt = ModelType::get_by_id($deleteId);
  if (validateDelete($mt)) {
    FlashMessage::add("Am șters tipul de model '{$mt->code}'.", 'success');
    Log::notice("Deleted model type {$mt->code} ({$mt->description})");
    $mt->delete();
    Util::redirectToSelf();
  }
}

// Load model type table data
$modelTypes = Model::factory('ModelType')->order_by_asc('code')->find_many();
$modelCounts = [];
$lexemeCounts = [];
$canDelete = [];
foreach ($modelTypes as $mt) {
  $numLexemes = Model::factory('Lexeme')->where('modelType', $mt->code)->count();
  $numDependants = Model::factory('ModelType')->where('canonical', $mt->code)->count();
  $modelCounts[] = Model::factory('FlexModel')->where('modelType', $mt->code)->count();
  $lexemeCounts[] = $numLexemes;
  $canDelete[] = ($numLexemes == 0) && ($numDependants <= 1);
}

Smart::assign([
  'canonicalModelTypes' => ModelType::loadCanonical(),
  'modelTypes' => (array)$modelTypes,
  'modelCounts' => $modelCounts,
  'lexemeCounts' => $lexemeCounts,
  'canDelete' => $canDelete,
  'showAddForm' => $showAddForm,
]);
Smart::display('model/listTypes.tpl');

/***************************************************************************/

function validateAdd($mt) {
  if (!$mt->code) {
    FlashMessage::add('Codul nu poate fi vid.');
  }
  if (ModelType::get_by_code($mt->code)) {
    FlashMessage::add("Codul '{$mt->code}' este deja folosit.");
  }
  if (!$mt->description) {
    FlashMessage::add('Descrierea nu poate fi vidă. Ea trebuie să indice partea de vorbire și este vizibilă la afișarea paradigmelor.');
  }
  return !FlashMessage::hasErrors();
}

function validateEdit($mt) {
  if (!$mt->description) {
    FlashMessage::add('Descrierea nu poate fi vidă. Ea trebuie să indice partea de vorbire și este vizibilă la afișarea paradigmelor.');
  }
  return !FlashMessage::hasErrors();
}

function validateDelete($mt) {
  $numLexemes = Model::factory('Lexeme')->where('modelType', $mt->code)->count();
  if ($numLexemes) {
    FlashMessage::add("Nu pot șterge tipul '{$mt->code}', deoarece este folosit de {$numLexemes} lexeme.");
  }
  $numDependants = Model::factory('ModelType')->where('canonical', $mt->code)->count();
  if ($numDependants > 1) {
    FlashMessage::add("Nu pot șterge tipul '{$mt->code}', deoarece este canonic pentru alte tipuri.");
  }
  return !FlashMessage::hasErrors();
}

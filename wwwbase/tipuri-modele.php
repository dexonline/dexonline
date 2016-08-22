<?php
require_once("../phplib/util.php"); 
util_assertModerator(PRIV_EDIT);

$showAddForm = util_getRequestParameter('add');
$editId = util_getRequestParameter('editId');
$deleteId = util_getRequestParameter('deleteId');
$submitAddButton = util_getRequestParameter('submitAddButton');
$submitEditButton = util_getRequestParameter('submitEditButton');
$id = util_getRequestParameter('id');
$code = util_getRequestParameter('code');
$canonical = util_getRequestParameter('canonical');
$description = util_getRequestParameter('description');

if ($showAddForm) {
  SmartyWrap::assign('addModelType', Model::factory('ModelType')->create());
}

if ($submitAddButton) {
  $mt = Model::factory('ModelType')->create();
  $mt->code = mb_strtoupper($code);
  $mt->canonical = $canonical;
  $mt->description = $description;
  if (validateAdd($mt)) {
    $mt->save();
    Log::notice("Created model type {$mt->code} ({$mt->description})");
    FlashMessage::add("Am adăugat tipul de model '{$mt->code}'.", 'success');
    util_redirect('tipuri-modele.php');
  } else {
    $showAddForm = true;
    SmartyWrap::assign('addModelType', $mt);
  }
}

if ($submitEditButton) {
  $mt = ModelType::get_by_id($id);
  $mt->description = $description;
  if (validateEdit($mt)) {
    $mt->save();
    Log::notice("Changed description for model type {$mt->code} ({$mt->description})");    
    FlashMessage::add('Am salvat descrierea.', 'success');
    util_redirect('tipuri-modele.php');
  } else {
    SmartyWrap::assign('editModelType', $mt);
  }
}

if ($editId) {
  // Load model type to be edited
  SmartyWrap::assign('editModelType', ModelType::get_by_id($editId));
}

if ($deleteId) {
  $mt = ModelType::get_by_id($deleteId);
  if (validateDelete($mt)) {
    FlashMessage::add("Am șters tipul de model '{$mt->code}'.", 'success');
    Log::notice("Deleted model type {$mt->code} ({$mt->description})");    
    $mt->delete();
    util_redirect('tipuri-modele.php');
  }
}

// Load model type table data
$modelTypes = Model::factory('ModelType')->order_by_asc('code')->find_many();
$modelCounts = array();
$lexemCounts = array();
$canDelete = array();
foreach ($modelTypes as $mt) {
  $numLexems = Model::factory('Lexem')->where('modelType', $mt->code)->count();
  $numDependants = Model::factory('ModelType')->where('canonical', $mt->code)->count();
  $modelCounts[] = Model::factory('FlexModel')->where('modelType', $mt->code)->count();
  $lexemCounts[] = $numLexems;
  $canDelete[] = ($numLexems == 0) && ($numDependants <= 1);
}

SmartyWrap::assign('canonicalModelTypes', ModelType::loadCanonical());
SmartyWrap::assign('modelTypes', $modelTypes);
SmartyWrap::assign('modelCounts', $modelCounts);
SmartyWrap::assign('lexemCounts', $lexemCounts);
SmartyWrap::assign('canDelete', $canDelete);
SmartyWrap::assign('showAddForm', $showAddForm);
SmartyWrap::display('tipuri-modele.tpl');

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
  $numLexems = Model::factory('Lexem')->where('modelType', $mt->code)->count();
  if ($numLexems) {
    FlashMessage::add("Nu pot șterge tipul '{$mt->code}', deoarece este folosit de {$numLexems} lexeme.");
  }
  $numDependants = Model::factory('ModelType')->where('canonical', $mt->code)->count();
  if ($numDependants > 1) {
    FlashMessage::add("Nu pot șterge tipul '{$mt->code}', deoarece este canonic pentru alte tipuri.");
  }
  return !FlashMessage::hasErrors();
}
  
?>

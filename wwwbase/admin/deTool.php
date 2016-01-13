<?php
require_once("../../phplib/util.php"); 
util_assertModerator(PRIV_EDIT);
util_assertNotMirror();

define('SOURCE_ID', 25); // Dicționarul enciclopedic

$definitionId = util_getRequestParameter('definitionId');
$jumpPrefix = util_getRequestParameterWithDefault('jumpPrefix', '');
$butTest = util_getRequestParameter('butTest');
$butSave = util_getRequestParameter('butSave');
$butNext = util_getRequestParameter('butNext');

// Load the next definition from DE
if ($definitionId) {
  $def = Definition::get_by_id($definitionId);
} else {
  $def = Model::factory('Definition')
       ->where('sourceId', SOURCE_ID)
       ->where('status', Definition::ST_ACTIVE)
       ->where_gte('lexicon', $jumpPrefix)
       ->order_by_asc('lexicon')
       ->order_by_asc('id')
       ->find_one();
}

if (!$def) {
  exit;
}

$next = Model::factory('Definition')
  ->where('sourceId', SOURCE_ID)
  ->where('status', Definition::ST_ACTIVE)
  ->where_raw('((lexicon > ?) or (lexicon = ? and id > ?))',
              [$def->lexicon, $def->lexicon, $def->id])
  ->order_by_asc('lexicon')
  ->order_by_asc('id')
  ->find_one();
$nextId = $next ? $next->id : 0;

$lexems = Model::factory('Lexem')
        ->select('Lexem.*')
        ->join('LexemDefinitionMap', 'Lexem.id = lexemId', 'ldm')
        ->where('ldm.definitionId', $def->id)
        ->order_by_asc('formNoAccent')
        ->find_many();
$lexemIds = util_objectProperty($lexems, 'id');

$models = [];
foreach ($lexems as $l) {
  $lms = $l->getLexemModels();
  $a = [];
  foreach ($lms as $lm) {
    $a[] = "{$lm->modelType}{$lm->modelNumber}";
  }
  $models[] = $a;
}

SmartyWrap::assign('def', $def);
SmartyWrap::assign('nextId', $nextId);
SmartyWrap::assign('lexemIds', $lexemIds);
SmartyWrap::assign('models', $models);
SmartyWrap::addCss('jqueryui', 'select2');
SmartyWrap::addJs('jquery', 'jqueryui', 'select2', 'select2Dev', 'deTool');
SmartyWrap::displayAdminPage('admin/deTool.tpl');

<?php
/**
 * Sends paradigm for inserting into lexeme/edit, without submitting the form
 **/
require_once '../../lib/Core.php';
User::mustHave(User::PRIV_EDIT);

$lexemeId = Request::get('lexemeId');
$lexemeForm = Request::getWithApostrophes('lexemeForm');
$modelType = Request::get('modelType');
$modelNumber = Request::get('modelNumber');
$restriction = Request::get('restriction');
$compound = Request::get('compound');
$needsAccent = Request::get('needsAccent');
$entryIds = Request::getArray('entryIds');

$notes = Request::get('notes');
$hasApheresis = Request::has('hasApheresis');
$hasApocope = Request::has('hasApocope');

$partIds = Request::getArray('partIds');
$declensions = Request::getArray('declensions');
$capitalized = Request::get('capitalized');
$accented = Request::get('accented');
$sourceIds = Request::getArray('sourceIds');
$tagIds = Request::getArray('tagIds');

$lexeme = Model::factory('Lexeme')->create();

$lexeme->id = $lexemeId;
$lexeme->setForm($lexemeForm);
$lexeme->modelType = $modelType;
$lexeme->restriction = $restriction;
$lexeme->compound = $compound;
$lexeme->notes = $notes;
$lexeme->hasApheresis = $hasApheresis;
$lexeme->hasApocope = $hasApocope;
$lexeme->noAccent = !$needsAccent;
$lexeme->setSourceNames($sourceIds);

// set inflection to contain vocative, if tag is present
$posTagName = Config::TAG_ANIMATE_LEXEME;
$posTag = Tag::get_by_value($posTagName);
$lexeme->setAnimate(in_array($posTag->id, $tagIds, true));

if ($lexeme->compound ) {
  $lexeme->modelNumber = 0;
  // create Fragments
  $fragments = [];
  foreach ($partIds as $i => $partId) {
    $fragments[] = Fragment::create(
      $partId, $declensions[$i], $capitalized[$i], $accented[$i], $i);
  }
  $lexeme->setFragments($fragments);
} else {
  $lexeme->modelNumber = $modelNumber;
}

if ($lexeme->validate()) {
  try {
    $lexeme->generateInflectedForms();
  } catch (ParadigmException $pe) {
    FlashMessage::add($pe->getMessage());
  }

}

//Smart::display('paradigm/paradigm.tpl');
if (FlashMessage::hasErrors()) {
  $output = Smart::fetch('bits/flashMessages.tpl');
} else {
  Smart::assign('lexeme', $lexeme);
  $output = Smart::fetch('paradigm/paradigm.tpl');
}
echo $output ;

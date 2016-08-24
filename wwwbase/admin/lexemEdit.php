<?php
require_once("../../phplib/util.php"); 
util_assertModerator(PRIV_EDIT | PRIV_STRUCT);
util_assertNotMirror();

handleLexemActions();

// We get some data as JSON because it is 2-dimensional (a list of lists)
// and PHP cannot parse the form data correctly.

// Lexem parameters
$lexemId = util_getRequestParameter('lexemId');
$lexemForm = util_getRequestParameter('lexemForm');
$lexemNumber = util_getRequestParameter('lexemNumber');
$lexemDescription = util_getRequestParameter('lexemDescription');
$lexemComment = util_getRequestParameter('lexemComment');
$needsAccent = util_getBoolean('needsAccent');
$main = util_getBoolean('main');
$stopWord = util_getBoolean('stopWord');
$hyphenations = util_getRequestParameter('hyphenations');
$pronunciations = util_getRequestParameter('pronunciations');
$entryId = util_getRequestParameter('entryId');
$tagIds = util_getRequestParameter('tagIds');

// Paradigm parameters
$modelType = util_getRequestParameter('modelType');
$modelNumber = util_getRequestParameter('modelNumber');
$restriction = util_getRequestParameter('restriction');
$sourceIds = util_getRequestParameterWithDefault('sourceIds', []);
$notes = util_getRequestParameter('notes');
$isLoc = util_getBoolean('isLoc');

// Button parameters
$refreshLexem = util_getRequestParameter('refreshLexem');
$saveLexem = util_getRequestParameter('saveLexem');

$lexem = Lexem::get_by_id($lexemId);
$original = Lexem::get_by_id($lexemId); // Keep a copy so we can test whether certain fields have changed

if ($refreshLexem || $saveLexem) {
  populate($lexem, $original, $lexemForm, $lexemNumber, $lexemDescription, $lexemComment,
           $needsAccent, $main, $stopWord, $hyphenations, $pronunciations, $entryId,
           $modelType, $modelNumber, $restriction, $notes, $isLoc, $sourceIds);

  if (validate($lexem, $original)) {
    // Case 1: Validation passed
    if ($saveLexem) {
      if (($original->modelType == 'VT') && ($lexem->modelType != 'VT')) {
        $original->deleteParticiple();
      }
      if (in_array($original->modelType, ['V', 'VT']) &&
          !in_array($lexem->modelType, ['V', 'VT'])) {
        $original->deleteLongInfinitive();
      }
      $lexem->deepSave();
      $lexem->regenerateDependentLexems();

      // Delete the old tags and add the new tags.
      LexemTag::delete_all_by_lexemId($lexem->id);
      foreach ($tagIds as $tagId) {
        $lt = Model::factory('LexemTag')->create();
        $lt->lexemId = $lexem->id;
        $lt->tagId = $tagId;
        $lt->save();
      }

      Log::notice("Saved lexem {$lexem->id} ({$lexem->formNoAccent})");
      util_redirect("lexemEdit.php?lexemId={$lexem->id}");
    }
  } else {
    // Case 2: Validation failed
  }
  // Case 1-2: Page was submitted
} else {
  // Case 3: First time loading this page
  $lexem->loadInflectedFormMap();

  $lts = LexemTag::get_all_by_lexemId($lexem->id);
  $tagIds = util_objectProperty($lts, 'tagId');
}

$tags = Model::factory('Tag')->order_by_asc('value')->find_many();

$canEdit = array(
  'general' => util_isModerator(PRIV_EDIT),
  'description' => util_isModerator(PRIV_EDIT),
  'form' => !$lexem->isLoc || util_isModerator(PRIV_LOC),
  'hyphenations' => util_isModerator(PRIV_STRUCT | PRIV_EDIT),
  'loc' => (int)util_isModerator(PRIV_LOC),
  'paradigm' => util_isModerator(PRIV_EDIT),
  'pronunciations' => util_isModerator(PRIV_STRUCT | PRIV_EDIT),
  'sources' => util_isModerator(PRIV_LOC | PRIV_EDIT),
  'stopWord' => util_isModerator(PRIV_ADMIN),
  'tags' => util_isModerator(PRIV_LOC | PRIV_EDIT),
);

// Prepare a list of model numbers, to be used in the paradigm drop-down.
$models = FlexModel::loadByType($lexem->modelType);

SmartyWrap::assign('lexem', $lexem);
SmartyWrap::assign('homonyms', Model::factory('Lexem')->where('formNoAccent', $lexem->formNoAccent)->where_not_equal('id', $lexem->id)->find_many());
SmartyWrap::assign('tags', $tags);
SmartyWrap::assign('tagIds', $tagIds);
SmartyWrap::assign('modelTypes', Model::factory('ModelType')->order_by_asc('code')->find_many());
SmartyWrap::assign('models', $models);
SmartyWrap::assign('canEdit', $canEdit);
SmartyWrap::assign('suggestHiddenSearchForm', true);
SmartyWrap::assign('suggestNoBanner', true);
SmartyWrap::addCss('jqueryui-smoothness', 'paradigm', 'select2');
SmartyWrap::addJs('jqueryui', 'select2', 'select2Dev', 'modelDropdown');
SmartyWrap::display('admin/lexemEdit.tpl');

/**************************************************************************/

// Populate lexem fields from request parameters.
function populate(&$lexem, &$original, $lexemForm, $lexemNumber, $lexemDescription, $lexemComment,
                  $needsAccent, $main, $stopWord, $hyphenations, $pronunciations, $entryId,
                  $modelType, $modelNumber, $restriction, $notes, $isLoc, $sourceIds) {
  $lexem->setForm(AdminStringUtil::formatLexem($lexemForm));
  $lexem->number = $lexemNumber;
  $lexem->description = AdminStringUtil::internalize($lexemDescription, false);
  $lexem->comment = trim(AdminStringUtil::internalize($lexemComment, false));
  // Sign appended comments
  if (StringUtil::startsWith($lexem->comment, $original->comment) &&
      $lexem->comment != $original->comment &&
      !StringUtil::endsWith($lexem->comment, ']]')) {
    $lexem->comment .= " [[" . session_getUser() . ", " . strftime("%d %b %Y %H:%M") . "]]";
  }
  $lexem->noAccent = !$needsAccent;
  $lexem->main = $main;
  $lexem->stopWord = $stopWord;
  $lexem->hyphenations = $hyphenations;
  $lexem->pronunciations = $pronunciations;
  $lexem->entryId = $entryId;

  $lexem->modelType = $modelType;
  $lexem->modelNumber = $modelNumber;
  $lexem->restriction = $restriction;
  $lexem->notes = $notes;
  $lexem->isLoc = $isLoc;
  $lexem->generateInflectedFormMap();

  // Create LexemSources
  $lexemSources = [];
  foreach ($sourceIds as $sourceId) {
    $ls = Model::factory('LexemSource')->create();
    $ls->sourceId = $sourceId;
    $lexemSources[] = $ls;
  }
  $lexem->setLexemSources($lexemSources);
}

function validate($lexem, $original) {
  if (!$lexem->form) {
    FlashMessage::add('Forma nu poate fi vidă.');
  }

  $numAccents = mb_substr_count($lexem->form, "'");
  // Note: we allow multiple accents for lexems like hárcea-párcea
  if ($numAccents && $lexem->noAccent) {
    FlashMessage::add('Ați indicat că lexemul nu necesită accent, dar forma conține un accent.');
  } else if (!$numAccents && !$lexem->noAccent) {
    FlashMessage::add('Adăugați un accent sau debifați câmpul „Necesită accent”.');
  }

  // Gather all different restriction - model type pairs
  $pairs = Model::factory('ConstraintMap')
         ->table_alias('cm')
         ->select_expr('binary cm.code', 'restr')
         ->select('mt.code', 'modelType')
         ->distinct()
         ->join('Inflection', ['cm.inflectionId', '=', 'i.id'], 'i')
         ->join('ModelType', ['i.modelType', '=', 'mt.canonical'], 'mt')
         ->find_array();
  $restrMap = [];
  foreach ($pairs as $p) {
    $restrMap[$p['restr']][$p['modelType']] = true;
  }

  for ($i = 0; $i < mb_strlen($lexem->restriction); $i++) {
    $c = StringUtil::getCharAt($lexem->restriction, $i);
    if (!isset($restrMap[$c])) {
      FlashMessage::add("Restricția <strong>$c</strong> este nedefinită.");
    } else if (!isset($restrMap[$c][$lexem->modelType])) {
      FlashMessage::add("Restricția <strong>$c</strong> nu se aplică modelului <strong>{$lexem->modelType}.</strong>");
    }
  }
  
  $ifs = $lexem->generateInflectedForms();
  if (!is_array($ifs)) {
    $infl = Inflection::get_by_id($ifs);
    FlashMessage::add(sprintf("Nu pot genera flexiunea '%s' conform modelului %s%s",
                              htmlentities($infl->description), $lexem->modelType, $lexem->modelNumber));
  }

  return !FlashMessage::hasErrors();
}

/* This page handles a lot of actions. Move the minor ones here so they don't clutter the preview/save actions,
   which are hairy enough by themselves. */
function handleLexemActions() {
  $lexemId = util_getRequestParameter('lexemId');
  $lexem = Lexem::get_by_id($lexemId);

  $deleteLexem = util_getRequestParameter('deleteLexem');
  if ($deleteLexem) {
    $homonyms = Model::factory('Lexem')->where('formNoAccent', $lexem->formNoAccent)->where_not_equal('id', $lexem->id)->find_many();
    $lexem->delete();
    SmartyWrap::assign('lexem', $lexem);
    SmartyWrap::assign('homonyms', $homonyms);
    SmartyWrap::displayAdminPage('admin/lexemDeleted.tpl');
    exit;
  }

  $cloneLexem = util_getRequestParameter('cloneLexem');
  if ($cloneLexem) {
    $newLexem = $lexem->cloneLexem();
    Log::notice("Cloned lexem {$lexem->id} ({$lexem->formNoAccent}), new id is {$newLexem->id}");
    util_redirect("lexemEdit.php?lexemId={$newLexem->id}");
  }
}

?>

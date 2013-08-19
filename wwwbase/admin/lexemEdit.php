<?php
require_once("../../phplib/util.php"); 
util_assertModerator(PRIV_EDIT | PRIV_STRUCT);
util_assertNotMirror();

handleLexemActions();

$lexemId = util_getRequestParameter('lexemId');
$lexemForm = util_getRequestParameter('lexemForm');
$lexemDescription = util_getRequestParameter('lexemDescription');
$lexemSourceIds = util_getRequestParameter('lexemSourceIds');
$lexemTags = util_getRequestParameter('lexemTags');
$lexemComment = util_getRequestParameter('lexemComment');
$lexemIsLoc = util_getBoolean('lexemIsLoc');
$needsAccent = util_getBoolean('needsAccent');
$modelType = util_getRequestParameter('modelType');
$modelNumber = util_getRequestParameter('modelNumber');
$restriction = util_getRequestCheckboxArray('restr', '');
$hyphenations = util_getRequestParameter('hyphenations');
$pronunciations = util_getRequestParameter('pronunciations');
$variantIds = util_getRequestCsv('variantIds');
$variantOfId = util_getRequestParameter('variantOfId');
$structSealed = util_getRequestIntParameter('structSealed');
$jsonMeanings = util_getRequestParameter('jsonMeanings');

$refreshLexem = util_getRequestParameter('refreshLexem');
$saveLexem = util_getRequestParameter('saveLexem');

$lexem = Lexem::get_by_id($lexemId);
$original = Lexem::get_by_id($lexemId); // Keep a copy so we can test whether certain fields have changed

if ($refreshLexem || $saveLexem) {
  // Populate lexem fields from request parameters.
  $lexem->form = AdminStringUtil::formatLexem($lexemForm);
  $lexem->formNoAccent = str_replace("'", '', $lexem->form);
  $lexem->description = AdminStringUtil::internalize($lexemDescription, false);
  $lexem->tags = AdminStringUtil::internalize($lexemTags, false);
  $lexem->comment = trim(AdminStringUtil::internalize($lexemComment, false));
  // Sign appended comments
  if (StringUtil::startsWith($lexem->comment, $original->comment) &&
      $lexem->comment != $original->comment &&
      !StringUtil::endsWith($lexem->comment, ']]')) {
    $lexem->comment .= " [[" . session_getUser() . ", " . strftime("%d %b %Y %H:%M") . "]]";
  }
  $lexem->isLoc = $lexemIsLoc;
  $lexem->noAccent = !$needsAccent;
  $lexem->modelType = $modelType;
  $lexem->modelNumber = $modelNumber;
  $lexem->restriction = $restriction;
  $lexem->hyphenations = $hyphenations;
  $lexem->pronunciations = $pronunciations;
  $lexem->variantOfId = $variantOfId ? $variantOfId : null;
  $variantOf = Lexem::get_by_id($lexem->variantOfId);
  $lexem->structSealed = $structSealed;
  $meanings = json_decode($jsonMeanings);
  $ifs = $lexem->generateParadigm();

  if (validate($lexem, $ifs, $variantOf, $variantIds, $meanings)) {
    // Case 1: Validation passed
    if ($saveLexem) {
      if ($original->modelType == 'VT' && $lexem->modelType != 'VT') {
        $lexem->deleteParticiple($original->modelNumber);
      }
      if (($original->modelType == 'VT' || $original->modelType == 'V') &&
          ($lexem->modelType != 'VT' && $lexem->modelType != 'V')) {
        $lexem->deleteLongInfinitive();
      }
      $lexem->save();
      Meaning::saveTree($meanings, $lexem);
      LexemSource::updateList(array('lexemId' => $lexem->id), 'sourceId', $lexemSourceIds);
      $lexem->updateVariants($variantIds);
      $lexem->regenerateParadigm(); // This generates AND saves the paradigm

      log_userLog("Edited lexem {$lexem->id} ({$lexem->form})");
      util_redirect("lexemEdit.php?lexemId={$lexem->id}");
    }
  } else {
    // Case 2: Validation failed
  }
  // Case 1-2: Page was submitted
    SmartyWrap::assign('variantIds', $variantIds);
    SmartyWrap::assign('meanings', Meaning::convertTree($meanings));
} else {
  // Case 3: First time loading this page
  $ifs = $lexem->generateParadigm();
  $lexemSourceIds = LexemSource::getForLexem($lexem);
  SmartyWrap::assign('variantIds', $lexem->getVariantIds());
  SmartyWrap::assign('meanings', Meaning::loadTree($lexem->id));
}

$definitions = Definition::loadByLexemId($lexem->id);
foreach ($definitions as $def) {
  // $def->internalRep = AdminStringUtil::expandAbbreviations($def->internalRep, $def->sourceId);
}
$searchResults = SearchResult::mapDefinitionArray($definitions);
$definitionLexem = mb_strtoupper(AdminStringUtil::internalize($lexem->form, false));
$meaningTags = Model::factory('MeaningTag')->order_by_asc('value')->find_many();

if (is_array($ifs)) {
  $ifMap = InflectedForm::mapByInflectionRank($ifs);
  SmartyWrap::assign('ifMap', $ifMap);
}

$canEdit = array(
  'general' => util_isModerator(PRIV_EDIT),
  'description' => !$lexem->isLoc || util_isModerator(PRIV_LOC),
  'form' => !$lexem->isLoc || util_isModerator(PRIV_LOC),
  'hyphenations' => !$lexem->structSealed || util_isModerator(PRIV_EDIT),
  'isLoc' => util_isModerator(PRIV_LOC),
  'meanings' => !$lexem->structSealed || util_isModerator(PRIV_EDIT),
  'paradigm' => util_isModerator(PRIV_LOC),
  'pronunciations' => !$lexem->structSealed || util_isModerator(PRIV_EDIT),
  'seal' => util_isModerator(PRIV_EDIT),
  'sources' => util_isModerator(PRIV_LOC | PRIV_EDIT),
  'tags' => util_isModerator(PRIV_LOC | PRIV_EDIT),
  'variants' => !$lexem->structSealed || util_isModerator(PRIV_EDIT),
);

SmartyWrap::assign('lexem', $lexem);
SmartyWrap::assign('lexemSourceIdMap', util_makeSet($lexemSourceIds));
SmartyWrap::assign('searchResults', $searchResults);
SmartyWrap::assign('definitionLexem', $definitionLexem);
SmartyWrap::assign('homonyms', Model::factory('Lexem')->where('formNoAccent', $lexem->formNoAccent)->where_not_equal('id', $lexem->id)->find_many());
SmartyWrap::assign('suggestedLexems', loadSuggestions($lexem, 5));
SmartyWrap::assign('restrS', FlexStringUtil::contains($lexem->restriction, 'S'));
SmartyWrap::assign('restrP', FlexStringUtil::contains($lexem->restriction, 'P'));
SmartyWrap::assign('restrU', FlexStringUtil::contains($lexem->restriction, 'U'));
SmartyWrap::assign('restrI', FlexStringUtil::contains($lexem->restriction, 'I'));
SmartyWrap::assign('restrT', FlexStringUtil::contains($lexem->restriction, 'T'));
SmartyWrap::assign('meaningTags', $meaningTags);
SmartyWrap::assign('modelTypes', Model::factory('ModelType')->order_by_asc('code')->find_many());
SmartyWrap::assign('models', FlexModel::loadByType($lexem->modelType));
SmartyWrap::assign('canEdit', $canEdit);
SmartyWrap::assign('allStatuses', util_getAllStatuses());
SmartyWrap::addCss('easyui', 'jqueryui', 'paradigm', 'select2', 'lexemEdit');
SmartyWrap::addJs('easyui', 'jqueryui', 'select2', 'select2Dev', 'lexemEdit');
SmartyWrap::assign('sectionTitle', "Editare lexem: {$lexem->form} {$lexem->modelType}{$lexem->modelNumber}{$lexem->restriction}");
SmartyWrap::displayAdminPage('admin/lexemEdit.ihtml');

/**************************************************************************/

function validate($lexem, $ifs, $variantOf, $variantIds, $meanings) {
  if (!$lexem->form) {
    FlashMessage::add('Forma nu poate fi vidă.');
  }

  $numAccents = mb_substr_count($lexem->form, "'");
  // Note: we allow multiple accents for lexems like hárcea-párcea
  if ($numAccents && $lexem->noAccent) {
    FlashMessage::add('Ați indicat că lexemul nu necesită accent, dar forma conține un accent.');
  } else if (!$numAccents && !$lexem->noAccent) {
    FlashMessage::add('Adăugați un accent sau debifați câmpul "Necesită accent".');
  }

  $hasS = false;
  $hasP = false;
  for ($i = 0; $i < mb_strlen($lexem->restriction); $i++) {
    $char = StringUtil::getCharAt($lexem->restriction, $i);
    if ($char == 'T' || $char == 'U' || $char == 'I') {
      if ($lexem->modelType != 'V' && $lexem->modelType != 'VT') {
        FlashMessage::add("Restricția <b>$char</b> se aplică numai verbelor");
      }
    } else if ($char == 'S') {
      if ($lexem->modelType == 'I' || $lexem->modelType == 'T') {
        FlashMessage::add("Restricția <b>S</b> nu se aplică modelului $lexem->modelType");
      }
      $hasS = true;
    } else if ($char == 'P') {
      if ($lexem->modelType == 'I' || $lexem->modelType == 'T') {
        FlashMessage::add("Restricția <b>P</b> nu se aplică modelului $lexem->modelType");
      }
      $hasP = true;
    } else {
      FlashMessage::add("Restricția <b>$char</b> este incorectă.");
    }
  }
  
  if ($hasS && $hasP) {
    FlashMessage::add("Restricțiile <b>S</b> și <b>P</b> nu pot coexista.");
  }

  if (!is_array($ifs)) {
    $infl = Inflection::get_by_id($ifs);
    FlashMessage::add(sprintf("Nu pot genera flexiunea '%s' conform modelului %s%s",
                              htmlentities($infl->description), $lexem->modelType, $lexem->modelNumber));
  }

  if ($variantOf && !empty($meanings)) {
    FlashMessage::add("Acest lexem este o variantă a lui {$variantOf} și nu poate avea el însuși sensuri.");
  }
  if ($variantOf && !empty($variantIds)) {
    FlashMessage::add("Acest lexem este o variantă a lui {$variantOf} și nu poate avea el însuși variante.");
  }
  if ($variantOf && ($variantOf->id == $lexem->id)) {
    FlashMessage::add("Lexemul nu poate fi variantă a lui însuși.");
  }

  foreach ($variantIds as $variantId) {
    $variant = Lexem::get_by_id($variantId);
    if ($variant->id == $lexem->id) {
      FlashMessage::add('Lexemul nu poate fi variantă a lui însuși.');
    }
    if ($variant->variantOfId && $variant->variantOfId != $lexem->id) {
      $other = Lexem::get_by_id($variant->variantOfId);
      FlashMessage::add("\"{$variant}\" este deja marcat ca variantă a lui \"{$other}\".");
    }
    $variantVariantCount = Model::factory('Lexem')->where('variantOfId', $variant->id)->count();
    if ($variantVariantCount) {
      FlashMessage::add("\"{$variant}\" are deja propriile lui variante.");
    }
    $variantMeaningCount = Model::factory('Meaning')->where('lexemId', $variant->id)->count();
    if ($variantMeaningCount) {
      FlashMessage::add("\"{$variant}\" are deja propriile lui sensuri.");
    }
  }

  return FlashMessage::getMessage() == null;
}

function loadSuggestions($lexem, $limit) {
  $query = $lexem->reverse;
  $lo = 0;
  $hi = mb_strlen($query);
  $result = array();

  while ($lo <= $hi) {
    $mid = (int)(($lo + $hi) / 2);
    $partial = mb_substr($query, 0, $mid);
    $lexems = Model::factory('Lexem')->where_like('reverse', "{$partial}%")->where_not_equal('modelType', 'T')->where_not_equal('id', $lexem->id)
      ->group_by('modelType')->group_by('modelNumber')->limit($limit)->find_many();
    
    if (count($lexems)) {
      $result = $lexems;
      $lo = $mid + 1;
    } else {
      $hi = $mid - 1;
    }
  }
  return $result;
}

function _cloneLexem($lexem) {
  $clone = Lexem::create($lexem->form, 'T', 1, '');
  $clone->comment = $lexem->comment;
  $clone->description = ($lexem->description) ? "CLONĂ {$lexem->description}" : "CLONĂ";
  $clone->noAccent = $lexem->noAccent;
  $clone->save();
    
  // Clone the definition list
  $ldms = LexemDefinitionMap::get_all_by_lexemId($lexem->id);
  foreach ($ldms as $ldm) {
    LexemDefinitionMap::associate($clone->id, $ldm->definitionId);
  }

  $clone->regenerateParadigm();
  return $clone;
}

/* This page handles a lot of actions. Move the minor ones here so they don't clutter the preview/save actions,
   which are hairy enough by themselves. */
function handleLexemActions() {
  $lexemId = util_getRequestParameter('lexemId');
  $lexem = Lexem::get_by_id($lexemId);

  $associateDefinitionId = util_getRequestParameter('associateDefinitionId');
  if ($associateDefinitionId) {
    LexemDefinitionMap::associate($lexem->id, $associateDefinitionId);
    util_redirect("lexemEdit.php?lexemId={$lexem->id}");
  }

  $dissociateDefinitionId = util_getRequestParameter('dissociateDefinitionId');
  if ($dissociateDefinitionId) {
    LexemDefinitionMap::dissociate($lexem->id, $dissociateDefinitionId);
    util_redirect("lexemEdit.php?lexemId={$lexem->id}");
  }

  $createDefinition = util_getRequestParameter('createDefinition');
  $miniDefTarget = util_getRequestParameter('miniDefTarget');
  if ($createDefinition) {
    $def = Model::factory('Definition')->create();
    $def->displayed = 0;
    $def->userId = session_getUserId();
    $def->sourceId = Source::get_by_shortName('Neoficial')->id;
    $def->lexicon = $lexem->formNoAccent;
    $def->internalRep =
      '@' . mb_strtoupper(AdminStringUtil::internalize($lexem->form, false)) .
      '@ v. @' . $miniDefTarget . '.@';
    $def->htmlRep = AdminStringUtil::htmlize($def->internalRep, $def->sourceId);
    $def->status = ST_ACTIVE;
    $def->save();

    LexemDefinitionMap::associate($lexem->id, $def->id);

    util_redirect("lexemEdit.php?lexemId={$lexem->id}");
  }

  $deleteLexem = util_getRequestParameter('deleteLexem');
  if ($deleteLexem) {
    $homonyms = Model::factory('Lexem')->where('formNoAccent', $lexem->formNoAccent)->where_not_equal('id', $lexem->id)->find_many();
    $lexem->delete();
    SmartyWrap::assign('lexem', $lexem);
    SmartyWrap::assign('homonyms', $homonyms);
    SmartyWrap::assign('sectionTitle', 'Confirmare ștergere lexem');
    SmartyWrap::displayAdminPage('admin/lexemDeleted.ihtml');
    exit;
  }

  $cloneLexem = util_getRequestParameter('cloneLexem');
  if ($cloneLexem) {
    $newLexem = _cloneLexem($lexem);
    log_userLog("Cloned lexem {$lexem->id} ({$lexem->form}), new id is {$newLexem->id}");
    util_redirect("lexemEdit.php?lexemId={$newLexem->id}");
  }

  $mergeLexem = util_getRequestParameter('mergeLexem');
  $mergeLexemId = util_getRequestParameter('mergeLexemId');
  if ($mergeLexem) {
    $other = Lexem::get_by_id($mergeLexemId);
    $defs = Definition::loadByLexemId($lexem->id);
    foreach ($defs as $def) {
      LexemDefinitionMap::associate($other->id, $def->id);
    }
    $lexem->delete();
    util_redirect("lexemEdit.php?lexemId={$other->id}");
  }
}

?>

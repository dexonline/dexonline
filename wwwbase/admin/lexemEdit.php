<?php
require_once("../../phplib/util.php"); 

util_assertModerator(PRIV_EDIT);
util_assertNotMirror();
setlocale(LC_ALL, "ro_RO.utf8");

$lexemId = util_getRequestParameter('lexemId');
$dissociateDefinitionId = util_getRequestParameter('dissociateDefinitionId');
$associateDefinitionId = util_getRequestParameter('associateDefinitionId');
$lexemForm = util_getRequestParameter('lexemForm');
$lexemDescription = util_getRequestParameter('lexemDescription');
$lexemSourceIds = util_getRequestParameter('lexemSourceIds');
$lexemTags = util_getRequestParameter('lexemTags');
$lexemComment = util_getRequestParameter('lexemComment');
$lexemIsLoc = util_getBoolean('lexemIsLoc');
$needsAccent = util_getBoolean('needsAccent');
$modelType = util_getRequestParameter('modelType');
$modelNumber = util_getRequestParameter('modelNumber');
$similarModel = util_getRequestParameter('similarModel');
$similarLexemId = util_getRequestParameter('similarLexemId');
$restriction = util_getRequestCheckboxArray('restr', '');
$miniDefTarget = util_getRequestParameter('miniDefTarget');

$refreshLexem = util_getRequestParameter('refreshLexem');
$saveLexem = util_getRequestParameter('saveLexem');
$cloneLexem = util_getRequestParameter('cloneLexem');
$deleteLexem = util_getRequestParameter('deleteLexem');
$createDefinition = util_getRequestParameter('createDefinition');

$lexem = Lexem::get_by_id($lexemId);
$original = Lexem::get_by_id($lexemId); // Keep a copy so we can test whether certain fields have changed

/*************************** various actions other than the save/refresh buttons ***************************/

if ($associateDefinitionId) {
  LexemDefinitionMap::associate($lexem->id, $associateDefinitionId);
  util_redirect("lexemEdit.php?lexemId={$lexem->id}");
}

if ($dissociateDefinitionId) {
  LexemDefinitionMap::dissociate($lexem->id, $dissociateDefinitionId);
  util_redirect("lexemEdit.php?lexemId={$lexem->id}");
}

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
  exit;
}

if ($deleteLexem) {
  $homonyms = Model::factory('Lexem')->where('formNoAccent', $lexem->formNoAccent)->where_not_equal('id', $lexem->id)->find_many();
  $lexem->delete();
  SmartyWrap::assign('lexem', $lexem);
  SmartyWrap::assign('homonyms', $homonyms);
  SmartyWrap::assign('sectionTitle', 'Confirmare ștergere lexem');
  SmartyWrap::displayAdminPage('admin/lexemDeleted.ihtml');
  return;
}

if ($cloneLexem) {
  $newLexem = _cloneLexem($lexem);
  log_userLog("Cloned lexem {$lexem->id} ({$lexem->form}), new id is {$newLexem->id}");
  util_redirect("lexemEdit.php?lexemId={$newLexem->id}");
}

if (!$similarModel && !$similarLexemId && !$refreshLexem && !$saveLexem) {
  RecentLink::createOrUpdate("Lexem: {$lexem}");
}

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

  // The new model type, number and restrictions can come from three sources:
  // $similarModel, $similarLexemId or ($modelType, $modelNumber,
  // $restriction) directly
  if ($similarModel !== null) {
    $parts = FlexModel::splitName($similarModel);
    $lexem->modelType = $parts[0];
    $lexem->modelNumber = $parts[1];
    $lexem->restriction = $parts[2];
  } else if ($similarLexemId) {
    $similarLexem = Lexem::get_by_id($similarLexemId);
    $lexem->modelType = $similarLexem->modelType;
    $lexem->modelNumber = $similarLexem->modelNumber;
    $lexem->restriction = $similarLexem->restriction;
  } else if ($modelType !== null) {
    $lexem->modelType = $modelType;
    $lexem->modelNumber = $modelNumber;
    $lexem->restriction = $restriction;
  }

  $ifs = $lexem->generateParadigm();

  if (validate($lexem, $ifs)) {
    if ($saveLexem) {
      if ($original->modelType == 'VT' && $lexem->modelType != 'VT') {
        $lexem->deleteParticiple($original->modelNumber);
      }
      if (($original->modelType == 'VT' || $original->modelType == 'V') &&
          ($lexem->modelType != 'VT' && $lexem->modelType != 'V')) {
        $lexem->deleteLongInfinitive();
      }
      $lexem->save();
      LexemSource::update($lexem->id, $lexemSourceIds);
      $lexem->regenerateParadigm(); // This generates AND saves the paradigm

      log_userLog("Edited lexem {$lexem->id} ({$lexem->form})");
      util_redirect("lexemEdit.php?lexemId={$lexem->id}");
    }
  }
} else {
  $ifs = $lexem->generateParadigm();
  $lexemSourceIds = LexemSource::getForLexem($lexem);
}

$definitions = Definition::loadByLexemId($lexem->id);
$searchResults = SearchResult::mapDefinitionArray($definitions);
$definitionLexem = mb_strtoupper(AdminStringUtil::internalize($lexem->form, false));

if (is_array($ifs)) {
  $ifMap = InflectedForm::mapByInflectionRank($ifs);
  SmartyWrap::assign('ifMap', $ifMap);
}

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
SmartyWrap::assign('modelTypes', Model::factory('ModelType')->order_by_asc('code')->find_many());
SmartyWrap::assign('models', FlexModel::loadByType($lexem->modelType));
SmartyWrap::assign('canEditForm', !$lexem->isLoc || util_isModerator(PRIV_LOC));
SmartyWrap::assign('allStatuses', util_getAllStatuses());
SmartyWrap::addCss('jqueryui', 'paradigm', 'select2');
SmartyWrap::addJs('jquery', 'jqueryui', 'struct', 'select2');
SmartyWrap::assign('sectionTitle', "Editare lexem: {$lexem->form} {$lexem->modelType}{$lexem->modelNumber}{$lexem->restriction}");
SmartyWrap::displayAdminPage('admin/lexemEdit.ihtml');

/**************************************************************************/

function validate($lexem, $ifs) {
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

?>

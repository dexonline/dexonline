<?php
require_once("../../phplib/util.php"); 
util_assertModerator(PRIV_EDIT | PRIV_STRUCT);
util_assertNotMirror();

handleLexemActions();

// Lexem parameters
$lexemId = util_getRequestParameter('lexemId');
$lexemForm = util_getRequestParameter('lexemForm');
$lexemNumber = util_getRequestParameter('lexemNumber');
$lexemDescription = util_getRequestParameter('lexemDescription');
$lexemComment = util_getRequestParameter('lexemComment');
$needsAccent = util_getBoolean('needsAccent');
$hyphenations = util_getRequestParameter('hyphenations');
$pronunciations = util_getRequestParameter('pronunciations');
$variantIds = util_getRequestCsv('variantIds');
$variantOfId = util_getRequestParameter('variantOfId');
$structStatus = util_getRequestIntParameter('structStatus');
$jsonMeanings = util_getRequestParameter('jsonMeanings');

// LexemModel parameters (arrays)
$modelType = util_getRequestParameter('modelType');
$modelNumber = util_getRequestParameter('modelNumber');
$restriction = util_getRequestParameter('restriction');
$sourceIds = util_getRequestParameter('lexemSourceIds');
$lmTags = util_getRequestParameter('lmTags');
$isLoc = util_getRequestParameter('isLoc');

// Button parameters
$refreshLexem = util_getRequestParameter('refreshLexem');
$saveLexem = util_getRequestParameter('saveLexem');

$lexem = Lexem::get_by_id($lexemId);
$original = Lexem::get_by_id($lexemId); // Keep a copy so we can test whether certain fields have changed

if ($refreshLexem || $saveLexem) {
  populate($lexem, $original, $lexemForm, $lexemNumber, $lexemDescription, $lexemComment, $needsAccent, $hyphenations,
           $pronunciations, $variantOfId, $structStatus, $modelType, $modelNumber, $restriction, $lmTags, $isLoc, $sourceIds);
  $meanings = json_decode($jsonMeanings);

  if (validate($lexem, $original, $variantIds, $meanings)) {
    // Case 1: Validation passed
    if ($saveLexem) {
      if ($original->hasModelType('VT') && !$lexem->hasModelType('VT')) {
        $original->deleteParticiple();
      }
      if (($original->hasModelType('VT') || $original->hasModelType('V')) &&
          (!$lexem->hasModelType('VT') && !$lexem->hasModelType('V'))) {
        $original->deleteLongInfinitive();
      }
      foreach ($original->getLexemModels() as $lm) {
        $lm->delete(); // This will also delete LexemSources and InflectedForms
      }
      $lexem->deepSave();
      Meaning::saveTree($meanings, $lexem);
      $lexem->updateVariants($variantIds);
      $lexem->regenerateDependentLexems();

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
  foreach ($lexem->getLexemModels() as $lm) {
    $lm->loadInflectedFormMap();
  }
  SmartyWrap::assign('variantIds', $lexem->getVariantIds());
  SmartyWrap::assign('meanings', Meaning::loadTree($lexem->id));
}

$definitions = Definition::loadByLexemId($lexem->id);
foreach ($definitions as $def) {
  $def->internalRepAbbrev = AdminStringUtil::expandAbbreviations($def->internalRep, $def->sourceId);
  $def->htmlRepAbbrev = AdminStringUtil::htmlize($def->internalRepAbbrev, $def->sourceId);
}
$searchResults = SearchResult::mapDefinitionArray($definitions);
$definitionLexem = mb_strtoupper(AdminStringUtil::internalize($lexem->form, false));
$meaningTags = Model::factory('MeaningTag')->order_by_asc('value')->find_many();

$ss = $lexem->structStatus;
$oss = $original->structStatus; // syntactic sugar
$canEdit = array(
  'general' => util_isModerator(PRIV_EDIT),
  'defStructured' => util_isModerator(PRIV_EDIT),
  'description' => util_isModerator(PRIV_EDIT),
  'form' => !$lexem->isLoc() || util_isModerator(PRIV_LOC),
  'hyphenations' => ($ss == Lexem::STRUCT_STATUS_IN_PROGRESS) || util_isModerator(PRIV_EDIT),
  'loc' => (int)util_isModerator(PRIV_LOC),
  'meanings' => ($ss == Lexem::STRUCT_STATUS_IN_PROGRESS) || util_isModerator(PRIV_EDIT),
  'paradigm' => util_isModerator(PRIV_EDIT),
  'pronunciations' => ($ss == Lexem::STRUCT_STATUS_IN_PROGRESS) || util_isModerator(PRIV_EDIT),
  'sources' => util_isModerator(PRIV_LOC | PRIV_EDIT),
  'structStatus' => ($oss == Lexem::STRUCT_STATUS_NEW) || ($oss == Lexem::STRUCT_STATUS_IN_PROGRESS) || util_isModerator(PRIV_EDIT),
  'tags' => util_isModerator(PRIV_LOC | PRIV_EDIT),
  'variants' => ($ss == Lexem::STRUCT_STATUS_IN_PROGRESS) || util_isModerator(PRIV_EDIT),
);

// Prepare a list of models for each LexemModel, to be used in the paradigm drop-down.
$models = array();
foreach ($lexem->getLexemModels() as $lm) {
  $models[] = FlexModel::loadByType($lm->modelType);
}

$stemLexemModel = LexemModel::create('T', 1);

SmartyWrap::assign('lexem', $lexem);
SmartyWrap::assign('lexemModels', $lexem->getLexemModels());
SmartyWrap::assign('stemLexemModel', $stemLexemModel);
SmartyWrap::assign('searchResults', $searchResults);
SmartyWrap::assign('definitionLexem', $definitionLexem);
SmartyWrap::assign('homonyms', Model::factory('Lexem')->where('formNoAccent', $lexem->formNoAccent)->where_not_equal('id', $lexem->id)->find_many());
SmartyWrap::assign('meaningTags', $meaningTags);
SmartyWrap::assign('modelTypes', Model::factory('ModelType')->order_by_asc('code')->find_many());
SmartyWrap::assign('models', $models);
SmartyWrap::assign('jsonSources', Source::getJson());
SmartyWrap::assign('modelsT', FlexModel::loadByType('T'));
SmartyWrap::assign('canEdit', $canEdit);
SmartyWrap::assign('allStatuses', util_getAllStatuses());
SmartyWrap::assign('structStatusNames', Lexem::$STRUCT_STATUS_NAMES);
SmartyWrap::addCss('jqueryui-smoothness', 'paradigm', 'select2', 'lexemEdit', 'windowEngine', 'textComplete');
SmartyWrap::addJs('jqueryui', 'select2', 'select2Dev', 'lexemEdit', 'windowEngine', 'cookie', 'modelDropdown', 'textComplete');
SmartyWrap::assign('sectionTitle', "Editare lexem: {$lexem->form} {$lexem->modelType}{$lexem->modelNumber}{$lexem->restriction}");
SmartyWrap::assign('noAdminHeader', true);
SmartyWrap::displayAdminPage('admin/lexemEdit.tpl');

/**************************************************************************/

// Populate lexem fields from request parameters.
function populate(&$lexem, &$original, $lexemForm, $lexemNumber, $lexemDescription, $lexemComment, $needsAccent, $hyphenations,
                  $pronunciations, $variantOfId, $structStatus, $modelType, $modelNumber, $restriction, $lmTags, $isLoc, $sourceIds) {
  $lexem->form = AdminStringUtil::formatLexem($lexemForm);
  $lexem->formNoAccent = str_replace("'", '', $lexem->form);
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
  $lexem->hyphenations = $hyphenations;
  $lexem->pronunciations = $pronunciations;
  $lexem->variantOfId = $variantOfId ? $variantOfId : null;
  $lexem->structStatus = $structStatus;

  // Create LexemModels and LexemSources
  $lexemModels = array();
  for ($i = 1; $i < count($modelType); $i++) {
    $lm = Model::factory('LexemModel')->create();
    $lm->lexemId = $lexem->id;
    $lm->setLexem($lexem); // Otherwise it will reload the original
    $lm->displayOrder = $i;
    $lm->modelType = $modelType[$i];
    $lm->modelNumber = $modelNumber[$i];
    $lm->restriction = $restriction[$i];
    $lm->tags = $lmTags[$i];
    $lm->isLoc = $isLoc[$i];
    $lm->generateInflectedFormMap();

    $lexemSources = array();
    foreach (explode(',', $sourceIds[$i]) as $sourceId) {
      $ls = Model::factory('LexemSource')->create();
      $ls->sourceId = $sourceId;
      $lexemSources[] = $ls;
    }
    $lm->setSources($lexemSources);

    $lexemModels[] = $lm;
  }
  $lexem->setLexemModels($lexemModels);
}

function validate($lexem, $original, $variantIds, $meanings) {
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

  foreach ($lexem->getLexemModels() as $lm) {
    $hasS = false;
    $hasP = false;
    for ($i = 0; $i < mb_strlen($lm->restriction); $i++) {
      $c = StringUtil::getCharAt($lm->restriction, $i);
      if ($c == 'T' || $c == 'U' || $c == 'I') {
        if ($lm->modelType != 'V' && $lm->modelType != 'VT') {
          FlashMessage::add("Restricția <b>$c</b> se aplică numai verbelor");
        }
      } else if ($c == 'S') {
        if ($lm->modelType == 'I' || $lm->modelType == 'T') {
          FlashMessage::add("Restricția <b>S</b> nu se aplică modelului $lm->modelType");
        }
        $hasS = true;
      } else if ($c == 'P') {
        if ($lm->modelType == 'I' || $lm->modelType == 'T') {
          FlashMessage::add("Restricția <b>P</b> nu se aplică modelului $lm->modelType");
        }
        $hasP = true;
      } else {
        FlashMessage::add("Restricția <b>$c</b> este incorectă.");
      }
    }
  
    if ($hasS && $hasP) {
      FlashMessage::add("Restricțiile <b>S</b> și <b>P</b> nu pot coexista.");
    }

    $ifs = $lm->generateInflectedForms();
    if (!is_array($ifs)) {
      $infl = Inflection::get_by_id($ifs);
      FlashMessage::add(sprintf("Nu pot genera flexiunea '%s' conform modelului %s%s",
                                htmlentities($infl->description), $lm->modelType, $lm->modelNumber));
    }
  }

  $variantOf = Lexem::get_by_id($lexem->variantOfId);
  if ($variantOf && !goodForVariantJson($meanings)) {
    FlashMessage::add("Acest lexem este o variantă a lui {$variantOf} și nu poate avea el însuși sensuri. " .
                      "Este permis doar un sens, fără conținut, pentru indicarea surselor și a registrelor de folosire.");
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
    $variantMeanings = Model::factory('Meaning')->where('lexemId', $variant->id)->find_many();
    if (!goodForVariant($variantMeanings)) {
      FlashMessage::add("\"{$variant}\" are deja propriile lui sensuri.");
    }
  }

  if (($lexem->structStatus == Lexem::STRUCT_STATUS_DONE) &&
      ($original->structStatus != Lexem::STRUCT_STATUS_DONE) &&
      !util_isModerator(PRIV_EDIT)) {
    FlashMessage::add("Doar moderatorii pot marca structurarea drept terminată. Vă rugăm să folosiți valoarea „așteaptă moderarea”.");
  }

  return FlashMessage::getMessage() == null;
}

/* Variants can only have one empty meaning, used to list the variant's sources. */
function goodForVariant($meanings) {
  if (empty($meanings)) {
    return true;
  }
  if (count($meanings) > 1) {
    return false;
  }
  $m = $meanings[0];
  $mss = MeaningSource::get_all_by_meaningId($m->id);
  $relations = Relation::get_all_by_meaningId($m->id);
  return count($mss) &&
    !$m->internalRep &&
    !$m->internalComment &&
    empty($relations);
}

/* Same, but for a JSON object. */
function goodForVariantJson($meanings) {
  if (empty($meanings)) {
    return true;
  }
  if (count($meanings) > 1) {
    return false;
  }

  $m = $meanings[0];
  if (!$m->sourceIds || $m->internalRep || $m->internalComment) {
    return false;
  }

  for ($i = 1; $i < Relation::NUM_TYPES; $i++) {
    if (!empty($m->relationIds[$i])) {
      return false;
    }
  }

  return true;
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
    SmartyWrap::displayAdminPage('admin/lexemDeleted.tpl');
    exit;
  }

  $cloneLexem = util_getRequestParameter('cloneLexem');
  if ($cloneLexem) {
    $newLexem = $lexem->cloneLexem();
    log_userLog("Cloned lexem {$lexem->id} ({$lexem->form}), new id is {$newLexem->id}");
    util_redirect("lexemEdit.php?lexemId={$newLexem->id}");
  }

  $mergeLexem = util_getRequestParameter('mergeLexem');
  $mergeLexemId = util_getRequestParameter('mergeLexemId');
  if ($mergeLexem) {
    $other = Lexem::get_by_id($mergeLexemId);
    if ($lexem->form != $other->form) {
      FlashMessage::add('Nu pot unifica lexemele deoarece accentele diferă. Rezolvați diferența și încercați din nou.');
      util_redirect("lexemEdit.php?lexemId={$lexem->id}");
    }
    $defs = Definition::loadByLexemId($lexem->id);
    foreach ($defs as $def) {
      LexemDefinitionMap::associate($other->id, $def->id);
    }

    // Add lexem models from $lexem to $other if the form is the same. Exclude T-type models.
    $displayOrder = count($other->getLexemModels());
    foreach ($lexem->getLexemModels() as $lm) {
      if ($lm->modelType != 'T' && !$other->hasModel($lm->modelType, $lm->modelNumber)) {
        $lm->lexemId = $other->id;
        $lm->displayOrder = ++$displayOrder;
        $lm->save();
      }
    }

    // Add meanings from $lexem to $other and renumber their displayOrder and breadcrumb
    // displayOrders are generated sequentially regardless of level.
    // Breadcrumbs follow levels so only their first part changes.
    $counter = Model::factory('Meaning')->where('lexemId', $other->id)->count();
    $numRoots = Model::factory('Meaning')->where('lexemId', $other->id)->where('parentId', 0)->count();
    $meanings = Model::factory('Meaning')->where('lexemId', $lexem->id)->order_by_asc('displayOrder')->find_many();
    foreach ($meanings as $m) {
      $m->lexemId = $other->id;
      $m->displayOrder = ++$counter;
      $parts = explode('.', $m->breadcrumb, 2);
      $parts[0] += $numRoots;
      $m->breadcrumb = implode('.', $parts);
      $m->save();
    }

    $lexem->delete();
    util_redirect("lexemEdit.php?lexemId={$other->id}");
  }
}

?>

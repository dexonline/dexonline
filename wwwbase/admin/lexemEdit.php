<?
require_once("../../phplib/util.php"); 
require_once("../../phplib/lexemSources.php"); 

util_assertModeratorStatus();
util_assertNotMirror();
setlocale(LC_ALL, "ro_RO");

$lexemId = util_getRequestParameter('lexemId');
$dissociateDefinitionId = util_getRequestParameter('dissociateDefinitionId');
$associateDefinitionId = util_getRequestParameter('associateDefinitionId');
$lexemForm = util_getRequestParameter('lexemForm');
$lexemDescription = util_getRequestParameter('lexemDescription');
$lexemSources = util_getRequestParameter('lexemSources');
$lexemTags = util_getRequestParameter('lexemTags');
$lexemComment = util_getRequestParameter('lexemComment');
$lexemIsLoc = util_getRequestParameter('lexemIsLoc');
$lexemNoAccent = util_getRequestParameter('lexemNoAccent');
$modelType = util_getRequestParameter('modelType');
$modelNumber = util_getRequestParameter('modelNumber');
$similarModel = util_getRequestParameter('similarModel');
$similarLexemName = util_getRequestParameter('similarLexemName');
$restriction = util_getRequestCheckboxArray('restr', '');
$miniDefTarget = util_getRequestParameter('miniDefTarget');

$refreshLexem = util_getRequestParameter('refreshLexem');
$updateLexem = util_getRequestParameter('updateLexem');
$cloneLexem = util_getRequestParameter('cloneLexem');
$deleteLexem = util_getRequestParameter('deleteLexem');
$createDefinition = util_getRequestParameter('createDefinition');

$lexem = Lexem::get("id = {$lexemId}");
$oldModelType = $lexem->modelType;
$oldModelNumber = $lexem->modelNumber;

if ($associateDefinitionId) {
  LexemDefinitionMap::associate($lexem->id, $associateDefinitionId);
  util_redirect("lexemEdit.php?lexemId={$lexem->id}");
}

if ($dissociateDefinitionId) {
  LexemDefinitionMap::dissociate($lexem->id, $dissociateDefinitionId);
  util_redirect("lexemEdit.php?lexemId={$lexem->id}");
}

if ($createDefinition) {
  $def = new Definition();
  $def->userId = session_getUserId();
  $def->sourceId = Source::get('shortName="Neoficial"')->id;
  $def->lexicon = $lexem->formNoAccent;
  $def->internalRep =
    '@' . text_unicodeToUpper(text_internalize($lexem->form, false)) .
    '@ v. @' . $miniDefTarget . '.@';
  $def->htmlRep = text_htmlize($def->internalRep);
  $def->status = ST_ACTIVE;
  $def->save();

  LexemDefinitionMap::associate($lexem->id, $def->id);

  util_redirect("lexemEdit.php?lexemId={$lexem->id}");
  exit;
}

if ($deleteLexem) {
  $homonyms = db_find(new Lexem(), "formNoAccent = '{$lexem->formNoAccent}' and id != {$lexem->id}");
  $lexem->delete();
  smarty_assign('lexem', $lexem);
  smarty_assign('homonyms', $homonyms);
  smarty_displayWithoutSkin('admin/lexemDeleted.ihtml');
  return;
}

if ($cloneLexem) {
  $newLexem = _cloneLexem($lexem);
  log_userLog("Cloned lexem {$lexem->id} ({$lexem->form}), new id is {$newLexem->id}");
  util_redirect("lexemEdit.php?lexemId={$newLexem->id}");
}

if (!$similarModel && !$similarLexemName && !$refreshLexem && !$updateLexem) {
  RecentLink::createOrUpdate("Lexem: {$lexem}");
}

if ($lexemForm !== null) {
  $oldUnaccented = $lexem->formNoAccent;
  $lexem->form = text_formatLexem($lexemForm);
  $lexem->formNoAccent = str_replace("'", '', $lexem->form);
  $lexem->reverse = text_reverse($lexem->formNoAccent);
  if ($lexem->formNoAccent != $oldUnaccented) {
    $lexem->modelType = 'T';
    $lexem->modelNumber = 1;
  }
}

if ($lexemDescription !== null) {
  $lexem->description = text_internalize($lexemDescription, false);
}

if ($lexemTags !== null) {
  $lexem->tags = text_internalize($lexemTags, false);
}

if ($lexemSources !== null) {
  $lexem->source = join(',', $lexemSources);
}	

if ($lexemComment !== null) {
  $newComment = trim(text_internalize($lexemComment, false));
  $oldComment = trim($lexem->comment);
  if (text_startsWith($newComment, $oldComment) &&
      $newComment != $oldComment &&
      !text_endsWith($newComment, ']]')) {
    $newComment .= " [[" . session_getUser() . ", " .
      strftime("%d %b %Y %H:%M") . "]]\n";
  } else if ($newComment) {
    $newComment .= "\n";
  }
  $lexem->comment = $newComment;
}

if ($lexemIsLoc !== null) {
  $lexem->isLoc = ($lexemIsLoc != '');
}

if ($lexemNoAccent !== null) {
  $lexem->noAccent = ($lexemNoAccent != '');
}

// The new model type, number and restrictions can come from three sources:
// $similarModel, $similarLexemName or ($modelType, $modelNumber,
// $restriction) directly
$errorMessage = '';
if ($similarModel !== null) {
  $parts = Model::splitName($similarModel);
  $lexem->modelType = $parts[0];
  $lexem->modelNumber = $parts[1];
  $lexem->restriction = $parts[2];
} else if ($similarLexemName) {
  $matches = Lexem::loadByExtendedName($similarLexemName);
  if (count($matches) == 1) {
    $similarLexem = $matches[0];
    $lexem->modelType = $similarLexem->modelType;
    $lexem->modelNumber = $similarLexem->modelNumber;
    $lexem->restriction = $similarLexem->restriction;
  } else {
    $errorMessage = (count($matches) == 0)
      ? "Lexemul <i>".htmlentities($similarLexemName)."</i> nu există."
      : "Lexemul <i>".htmlentities($similarLexemName)."</i> este ambiguu.";
  }
} else if ($modelType !== null) {
  $lexem->modelType = $modelType;
  $lexem->modelNumber = $modelNumber;
  $lexem->restriction = $restriction;
}

if (!$errorMessage) {
  $errorMessage = validate($lexem);
}

if (!$errorMessage) {
  $errorMessage = validateRestriction($lexem->modelType, $lexem->restriction);
}

if ($updateLexem && !$errorMessage) {
  if ($oldModelType == 'VT' && $lexem->modelType != 'VT') {
    $lexem->deleteParticiple($oldModelNumber);
  }
  if (($oldModelType == 'VT' || $oldModelType == 'V') &&
      ($lexem->modelType != 'VT' && $lexem->modelType != 'V')) {
    $lexem->deleteLongInfinitive();
  }
  $lexem->save();
  // There are two reasons to regenerate the paradigm: the model has changed
  // or the form has changed. It's easier to do it every time.
  $lexem->regenerateParadigm();

  log_userLog("Edited lexem {$lexem->id} ({$lexem->form})");
  util_redirect("lexemEdit.php?lexemId={$lexem->id}");
}

$definitions = Definition::loadByLexemId($lexem->id);
$searchResults = SearchResult::mapDefinitionArray($definitions);
$definitionLexem = text_unicodeToUpper(text_internalize($lexem->form, false));

// Generate new inflected forms, but do not overwrite the old ones.
$ifs = $lexem->generateParadigm();
if (!is_array($ifs)) {
  $infl = Inflection::get("id = {$ifs}");
  if (!$errorMessage) {
    $errorMessage = "Nu pot genera inflexiunea '".htmlentities($infl->description)."' " .
      "conform modelului {$lexem->modelType}{$lexem->modelNumber}.";
  }
} else {
  $ifMap = InflectedForm::mapByInflectionId($ifs);
  smarty_assign('ifMap', $ifMap);
  smarty_assign('searchResults', $searchResults);
}

$models = Model::loadByType($lexem->modelType);

$sources = getSourceArrayChecked($lexem->source);
$sourceNames = getNamesOfSources($lexem->source);

smarty_assign('lexem', $lexem);
smarty_assign('sources', $sources);
smarty_assign('sourceNames', $sourceNames);
smarty_assign('searchResults', $searchResults);
smarty_assign('definitionLexem', $definitionLexem);
smarty_assign('homonyms', db_find(new Lexem(), "formNoAccent = '{$lexem->formNoAccent}' and id != {$lexem->id}"));
smarty_assign('suggestedLexems', loadSuggestions($lexem, 5));
smarty_assign('restrS', text_contains($lexem->restriction, 'S'));
smarty_assign('restrP', text_contains($lexem->restriction, 'P'));
smarty_assign('restrU', text_contains($lexem->restriction, 'U'));
smarty_assign('restrI', text_contains($lexem->restriction, 'I'));
smarty_assign('restrT', text_contains($lexem->restriction, 'T'));
smarty_assign('modelTypes', db_find(new ModelType(), '1 order by code'));
smarty_assign('models', $models);
smarty_assign('allStatuses', util_getAllStatuses());
smarty_assign('errorMessage', $errorMessage);
smarty_assign('recentLinks', RecentLink::loadForUser());
smarty_displayWithoutSkin('admin/lexemEdit.ihtml');

function validate($lexem) {
  if (!$lexem->form) {
    return 'Forma nu poate fi vidă.';
  }
  $numAccents = mb_substr_count($lexem->form, "'");
  // Note: we allow multiple accents for lexems like hárcea-párcea
  if ($numAccents && $lexem->noAccent) {
    return 'Ați indicat că lexemul nu necesită accent, dar forma conține un accent.';
  } else if (!$numAccents && !$lexem->noAccent) {
    return 'Adăugați un accent sau bifați câmpul "Nu necesită accent".';
  }
  return null;
}

function validateRestriction($modelType, $restriction) {
  $hasS = false;
  $hasP = false;
  for ($i = 0; $i < mb_strlen($restriction); $i++) {
    $char = text_getCharAt($restriction, $i);
    if ($char == 'T' || $char == 'U' || $char == 'I') {
      if ($modelType != 'V' && $modelType != 'VT') {
        return "Restricția <b>$char</b> se aplică numai verbelor";
      }
    } else if ($char == 'S') {
      if ($modelType == 'I' || $modelType == 'T') {
        return "Restricția S nu se aplică modelului $modelType";
      }
      $hasS = true;
    } else if ($char == 'P') {
      if ($modelType == 'I' || $modelType == 'T') {
        return "Restricția P nu se aplică modelului $modelType";
      }
      $hasP = true;
    } else {
      return "Restricția <b>$char</b> este incorectă.";
    }
  }
  
  if ($hasS && $hasP) {
    return "Restricțiile <b>S</b> și <b>P</b> nu pot coexista.";
  }
  return null;
}

function loadSuggestions($lexem, $limit) {
  $query = $lexem->reverse;
  $lo = 0;
  $hi = mb_strlen($query);
  $result = array();

  while ($lo <= $hi) {
    $mid = (int)(($lo + $hi) / 2);
    $partial = mb_substr($query, 0, $mid);
    $lexems = db_find(new Lexem(), "reverse like '{$partial}%' and modelType != 'T' and id != {$lexem->id} group by modelType, modelNumber limit {$limit}");
    
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
  $clone = new Lexem($lexem->form, 'T', 1, '');
  $clone->comment = $lexem->comment;
  $clone->description = ($lexem->description) ? "CLONĂ {$lexem->description}" : "CLONĂ";
  $clone->noAccent = $lexem->noAccent;
  $clone->save();
    
  // Clone the definition list
  $ldms = db_find(new LexemDefinitionMap(), "lexemId = {$lexem->id}");
  foreach ($ldms as $ldm) {
    LexemDefinitionMap::associate($clone->id, $ldm->definitionId);
  }

  $clone->regenerateParadigm();
  return $clone;
}

?>

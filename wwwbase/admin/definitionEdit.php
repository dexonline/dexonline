<?php
require_once("../../phplib/util.php"); 
util_assertModerator(PRIV_EDIT);
util_assertNotMirror();

$isOCR = null;
$nextOcrBut = util_getRequestParameter('but_next_ocr');
$definitionId = util_getRequestIntParameter('definitionId');
if($definitionId) {
    $lexemIds = util_getRequestCsv('lexemIds');
    $sourceId = util_getRequestIntParameter('source');
    $similarSource = util_getRequestParameter('similarSource');
    $internalRep = util_getRequestParameter('internalRep');
    $status = util_getRequestIntParameterWithDefault('status', null);
    $commentContents = util_getRequestParameter('commentContents');
    $preserveCommentUser = util_getRequestParameter('preserveCommentUser');
}
else {
    $definitionId = null;
    $lexemIds = null;
    $sourceId = null;
    $similarSource = 0;
    $internalRep = null;
    $status = null;
    $commentContents = null;
    $preserveCommentUser = null;
    $isOCR = 1;
}
$acceptButton = util_getRequestParameter('but_accept');
$moveButton = util_getRequestParameter('but_move');
$hasErrors = false;

if (!$definitionId) {
  if ($isOCR) {
    //find definitions assigned to user
    $ocr = Model::factory('OCR')->where('status', 'raw')->where('editorId', session_getUserId())->order_by_asc('dateModified')->order_by_asc('id')->find_one();
    // find definitions assigned to noone
    if (!$ocr || !$ocr->id) {
      $ocr = Model::factory('OCR')->where('status', 'raw')->where_null('editorId')->order_by_asc('dateModified')->order_by_asc('id')->find_one();
    }
    if (!$ocr || !$ocr->id) {
      echo("Lista cu definiții OCR este goală.");
      return;
    }
    $ambiguousMatches = array();
    $sourceId = $ocr->sourceId;
    $def = AdminStringUtil::internalizeDefinition($ocr->ocrText, $sourceId, $ambiguousMatches);

    $definition = Model::factory('Definition')->create();
    $definition->status = ST_PENDING;
    $definition->userId = session_getUserId();
    $definition->sourceId = $sourceId;
    $definition->similarSource = $similarSource;
    $definition->internalRep = $def;
    $definition->htmlRep = AdminStringUtil::htmlize($def, $sourceId);
    $definition->lexicon = AdminStringUtil::extractLexicon($definition);
    $definition->abbrevReview = count($ambiguousMatches) ? ABBREV_AMBIGUOUS : ABBREV_REVIEW_COMPLETE;
    $definition->save();
    $definitionId = $definition->id;
    $ocr->definitionId = $definitionId;
    $ocr->editorId = session_getUserId();
    $ocr->status = 'published';
    $ocr->save();
  } 
  else {
    return;
  }
}

if (!($definition = Definition::get_by_id($definitionId))) {
  return;
}

$comment = Model::factory('Comment')->where('definitionId', $definitionId)->where('status', ST_ACTIVE)->find_one();
$commentUser = $comment ? User::get_by_id($comment->userId) : null;
$oldInternalRep = $definition->internalRep;

if ($internalRep) {
  $errors = array();
  $definition->internalRep = AdminStringUtil::internalizeDefinition($internalRep, $sourceId);
  $definition->htmlRep = AdminStringUtil::htmlize($definition->internalRep, $sourceId, $errors);
  if (!empty($errors)) {
    $hasErrors = true;
    foreach ($errors as $error) {
      FlashMessage::add($error);
    }
  }
}
if (isset($status)) {
  $definition->status = (int)$status;
}
if ($sourceId) {
  $definition->sourceId = (int)$sourceId;
}

//ugly workaround - TBD a better solution
if ($_POST) {
  if ($similarSource) {
    $definition->similarSource = 1;
  }
  else {
    $definition->similarSource = 0;
  }
}

if ($internalRep || $sourceId) {
  $definition->lexicon = AdminStringUtil::extractLexicon($definition);
}

if (count($lexemIds)) {
  $lexems = array();
  $ldms = array();
  foreach ($lexemIds as $lexemId) {
    if (StringUtil::startsWith($lexemId, '@')) {
      // create a new lexem
      $l = Lexem::deepCreate(substr($lexemId, 1), 'T', '1');
      $l->deepSave();
    } else {
      $l = Lexem::get_by_id($lexemId);
    }
    $lexems[] = $l;
    $ldms[] = LexemDefinitionMap::create($l->id, $definitionId);
  }
} else {
  $lexems = Model::factory('Lexem')->select('Lexem.*')->join('LexemDefinitionMap', 'Lexem.id = lexemId', 'ldm')
    ->where('ldm.definitionId', $definitionId)->find_many();
  $lexemIds = util_objectProperty($lexems, 'id');
}

if ($commentContents) {
  if (!$comment) {
    $comment = Model::factory('Comment')->create();
    $comment->status = ST_ACTIVE;
    $comment->definitionId = $definitionId;
  }
  $newContents = AdminStringUtil::internalizeDefinition($commentContents, $sourceId);
  if ($newContents != $comment->contents) {
    $comment->contents = $newContents;
    $comment->htmlContents = AdminStringUtil::htmlize($comment->contents, $sourceId);
    if (!$preserveCommentUser) {
      $comment->userId = session_getUserId();
    }
  }
} else if ($comment) {
  // User wiped out the existing comment, set status to DELETED.
  $comment->status = ST_DELETED;
  $comment->userId = session_getUserId();  
}

if (($acceptButton || $moveButton) && !$hasErrors) {
  // The only difference between these two is that but_move also changes the
  // status to Active
  if ($moveButton) {
    $definition->status = ST_ACTIVE;
  }
    
  // Accept the definition and delete the typos associated with it.
  $definition->save();
  db_execute("delete from Typo where definitionId = {$definition->id}");
  if ($comment) {
    $comment->save();
  }

  if ($definition->status == ST_DELETED) {
    // If by deleting this definition, any associated lexems become unassociated, delete them
    $ldms = LexemDefinitionMap::get_all_by_definitionId($definition->id);
    db_execute("delete from LexemDefinitionMap where definitionId = {$definition->id}");

    foreach ($ldms as $ldm) {
      $l = Lexem::get_by_id($ldm->lexemId);
      $otherLdms = LexemDefinitionMap::get_all_by_lexemId($l->id);
      if (!$l->isLoc() && !count($otherLdms)) {
        $l->delete();
      }
    }
  } else {
    //$ldms = LexemDefinitionMap::get_all_by_definitionId($definitionId); // FIXME 
    db_execute("delete from LexemDefinitionMap where definitionId = {$definitionId}");
    foreach ($ldms as $ldm) {
      $ldm->save();
    }
  }
    
  log_userLog("Edited definition {$definition->id} ({$definition->lexicon})");
  util_redirect('definitionEdit.php?definitionId=' . $definitionId);
}
else if ($nextOcrBut && !$hasErrors) {
  //TODO: check if definition has lexems
  if ($ldms) {
    foreach ($ldms as $ldm) {
      $ldm->save();
    }
  }
  $definition->save();
  log_userLog("Edited OCR definition {$definition->id} ({$definition->lexicon})");
  util_redirect('definitionEdit.php');
}

$source = Source::get_by_id($definition->sourceId);

if (!$acceptButton && !$moveButton) {
  // If a button was pressed, then this is a POST request and the URL
  // does not contain the definition ID.
  RecentLink::createOrUpdate(sprintf("Definiție: %s (%s)", $definition->lexicon, $source->shortName));
}

SmartyWrap::assign('isOCR', $isOCR);
if ($definitionId) {
  SmartyWrap::assign('definitionId', $definitionId);
}
SmartyWrap::assign('def', $definition);
SmartyWrap::assign('source', $source);
SmartyWrap::assign('sim', SimilarRecord::create($definition, $lexemIds));
SmartyWrap::assign('user', User::get_by_id($definition->userId));
SmartyWrap::assign('comment', $comment);
SmartyWrap::assign('commentUser', $commentUser);
SmartyWrap::assign('lexemIds', $lexemIds);
SmartyWrap::assign('typos', Typo::get_all_by_definitionId($definition->id));
SmartyWrap::assign('homonyms', loadSetHomonyms($lexems));
SmartyWrap::assign("allStatuses", util_getAllStatuses());
SmartyWrap::assign("allModeratorSources", Model::factory('Source')->where('canModerate', true)->order_by_asc('displayOrder')->find_many());
SmartyWrap::assign('recentLinks', RecentLink::loadForUser());
SmartyWrap::addCss('jqueryui', 'select2');
SmartyWrap::addJs('jquery', 'jqueryui', 'select2', 'select2Dev', 'definitionEdit');
SmartyWrap::displayAdminPage('admin/definitionEdit.tpl');

/**
 * Load all lexems having the same form as one of the given lexems, but exclude the given lexems.
 **/
function loadSetHomonyms($lexems) {
  if (count($lexems) == 0) {
    return array();
  }
  $names = util_objectProperty($lexems, 'formNoAccent');
  $ids = util_objectProperty($lexems, 'id');
  return Model::factory('Lexem')->where_in('formNoAccent', $names)->where_not_in('id', $ids)->find_many();
}

?>

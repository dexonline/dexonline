<?php
require_once("../../phplib/util.php"); 
util_assertModerator(PRIV_EDIT);
util_assertNotMirror();

$definitionId = util_getRequestIntParameter('definitionId');
$lexemIds = util_getRequestCsv('lexemIds');
$sourceId = util_getRequestIntParameter('source');
$internalRep = util_getRequestParameter('internalRep');
$status = util_getRequestIntParameterWithDefault('status', null);
$commentContents = util_getRequestParameter('commentContents');
$preserveCommentUser = util_getRequestParameter('preserveCommentUser');
$refreshButton = util_getRequestParameter('but_refresh');
$acceptButton = util_getRequestParameter('but_accept');
$moveButton = util_getRequestParameter('but_move');
$hasErrors = false;

if (!$definitionId) {
  return;
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
if ($internalRep || $sourceId) {
  $definition->lexicon = AdminStringUtil::extractLexicon($definition);
}
if (count($lexemIds)) {
  $lexems = array();
  $ldms = array();
  foreach ($lexemIds as $lexemId) {
    $l = Lexem::get_by_id($lexemId);
    $lexems[] = $l;
    $ldms[] = LexemDefinitionMap::create($lexemId, $definitionId);
  }
} else {
  $lexems = Model::factory('Lexem')->select('Lexem.*')->join('LexemDefinitionMap', 'Lexem.id = lexemId', 'ldm')
    ->where('ldm.definitionId', $definitionId)->find_many();
  $lexemIds = util_objectProperty($lexems, 'id');
}

if ($commentContents) {
  if (!$comment) {
    $comment = Model::factory('comment')->create();
    $commend->status = ST_ACTIVE;
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
      if (!$l->isLoc && !count($otherLdms)) {
        $l->delete();
      }
    }
  } else {
    db_execute("delete from LexemDefinitionMap where definitionId = {$definitionId}");
    foreach ($ldms as $ldm) {
      $ldm->save();
    }
  }
    
  log_userLog("Edited definition {$definition->id} ({$definition->lexicon})");
  util_redirect('definitionEdit.php?definitionId=' . $definitionId);
}

$source = Source::get_by_id($definition->sourceId);

if (!$refreshButton && !$acceptButton && !$moveButton) {
  // If a button was pressed, then this is a POST request and the URL
  // does not contain the definition ID.
  RecentLink::createOrUpdate(sprintf("Definiție: %s (%s)", $definition->lexicon, $source->shortName));
}

SmartyWrap::assign('def', $definition);
SmartyWrap::assign('source', $source);
SmartyWrap::assign('user', User::get_by_id($definition->userId));
SmartyWrap::assign('comment', $comment);
SmartyWrap::assign('commentUser', $commentUser);
SmartyWrap::assign('lexemIds', $lexemIds);
SmartyWrap::assign('typos', Typo::get_all_by_definitionId($definition->id));
SmartyWrap::assign('homonyms', loadSetHomonyms($lexems));
SmartyWrap::assign("allStatuses", util_getAllStatuses());
SmartyWrap::assign("allModeratorSources", Model::factory('Source')->where('canModerate', true)->order_by_asc('displayOrder')->find_many());
SmartyWrap::assign('recentLinks', RecentLink::loadForUser());
SmartyWrap::assign('sectionTitle', "Editare definiție: {$definition->id}");
SmartyWrap::addCss('jqueryui', 'select2');
SmartyWrap::addJs('jquery', 'jqueryui', 'struct', 'select2');
SmartyWrap::displayAdminPage('admin/definitionEdit.ihtml');

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

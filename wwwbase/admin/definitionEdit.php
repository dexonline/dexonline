<?php
require_once("../../phplib/util.php"); 
util_assertModerator(PRIV_EDIT);
util_assertNotMirror();

$definitionId = util_getRequestIntParameter('definitionId');
$lexemNames = util_getRequestParameter('lexemName');
$associateLexemId = util_getRequestParameter('associateLexemId');
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

if ($associateLexemId) {
  LexemDefinitionMap::associate($associateLexemId, $definitionId);
  util_redirect("definitionEdit.php?definitionId={$definitionId}");
}

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
if ($lexemNames) {
  $lexems = array();
  $lexemIds = array();
  $ldms = array();
  foreach ($lexemNames as $lexemName) {
    $lexemName = trim($lexemName);
    if ($lexemName) {
      $matches = Lexem::loadByExtendedName($lexemName);
      if (count($matches) >= 1) {
        foreach ($matches as $match) {
          if (!in_array($match->id, $lexemIds)) {
            $lexemIds[] = $match->id;
            $lexems[] = $match;
            $ldms[] = LexemDefinitionMap::create($match->id, $definitionId);
          }
        }
      } else {
        $hasErrors = true;
        FlashMessage::add("Lexemul <i>".htmlentities($lexemName)."</i> nu există. Folosiți lista de sugestii pentru a-l corecta.");
        $lexems[] = Lexem::create($lexemName, 0, '', '');
        // We won't be needing $ldms since there is an error.
      }
    }
  }
} else {
  $lexems = Model::factory('Lexem')->select('Lexem.*')->join('LexemDefinitionMap', 'Lexem.id = lexemId')->where('definitionId', $definitionId)->find_many();
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
    $ldms = LexemDefinitionMap::get_all_by_definitionId($def->id);
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

smarty_assign('def', $definition);
smarty_assign('source', $source);
smarty_assign('user', User::get_by_id($definition->userId));
smarty_assign('comment', $comment);
smarty_assign('commentUser', $commentUser);
smarty_assign('lexems', $lexems);
smarty_assign('typos', Typo::get_all_by_definitionId($definition->id));
smarty_assign('homonyms', loadSetHomonyms($lexems));
smarty_assign("allStatuses", util_getAllStatuses());
smarty_assign("allModeratorSources", Model::factory('Source')->where('canModerate', true)->order_by_asc('displayOrder')->find_many());
smarty_assign('recentLinks', RecentLink::loadForUser());
smarty_assign('sectionTitle', "Editare definiție: {$definition->id}");
smarty_addCss('autocomplete');
smarty_addJs('jquery', 'autocomplete');
smarty_displayAdminPage('admin/definitionEdit.ihtml');

/**
 * Load all lexems having the same form as one of the given lexems, but exclude the given lexems.
 **/
function loadSetHomonyms($lexems) {
  if (count($lexems) == 0) {
    return array();
  }
  $names = array();
  $ids = array();
  foreach ($lexems as $l) {
    $names[] = $l->formNoAccent;
    $ids[] = $l->id;
  }
  return Model::factory('Lexem')->where_in('formNoAccent', $names)->where_not_in('id', $ids)->find_many();
}

?>

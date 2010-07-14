<?
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
$refreshButton = util_getRequestParameter('but_refresh');
$acceptButton = util_getRequestParameter('but_accept');
$moveButton = util_getRequestParameter('but_move');
$hasErrors = false;

if (!$definitionId) {
  return;
}

$definition = Definition::get("id = {$definitionId}");
$comment = Comment::get("definitionId = '{$definitionId}' and status = " . ST_ACTIVE);
$oldInternalRep = $definition->internalRep;

if ($associateLexemId) {
  LexemDefinitionMap::associate($associateLexemId, $definitionId);
  util_redirect("definitionEdit.php?definitionId={$definitionId}");
}

if ($internalRep) {
  $errors = array();
  $definition->internalRep = text_internalizeDefinition($internalRep, $sourceId);
  $definition->htmlRep = text_htmlize($definition->internalRep, $sourceId, $errors);
  if (!empty($errors)) {
    $hasErrors = true;
    foreach ($errors as $error) {
      session_setFlash($error);
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
  $definition->lexicon = text_extractLexicon($definition);
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
            $ldms[] = new LexemDefinitionMap($match->id, $definitionId);
          }
        }
      } else {
        $hasErrors = true;
        session_setFlash("Lexemul <i>".htmlentities($lexemName)."</i> nu există. Folosiți lista de sugestii pentru a-l corecta.");
        $lexems[] = new Lexem($lexemName, 0, '', '');
        // We won't be needing $ldms since there is an error.
      }
    }
  }
} else {
  $dbResult = db_execute("select Lexem.* from Lexem, LexemDefinitionMap where Lexem.id = lexemId and definitionId = {$definitionId}");
  $lexems = db_getObjects(new Lexem(), $dbResult);
}

if ($commentContents) {
  if (!$comment) {
    $comment = new Comment();
    $comment->definitionId = $definitionId;
  }
  if ($commentContents != $comment->contents) {
    $comment->userId = session_getUserId();
    $comment->contents = text_internalizeDefinition($commentContents, $sourceId);
    $comment->htmlContents = text_htmlize($comment->contents, $sourceId);
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
    $ldms = db_find(new LexemDefinitionMap(), "definitionId = {$definition->id}");
    db_execute("delete from LexemDefinitionMap where definitionId = {$definition->id}");

    foreach ($ldms as $ldm) {
      $l = Lexem::get("id = {$ldm->lexemId}");
      $otherLdms = db_find(new LexemDefinitionMap(), "lexemId = {$l->id}");
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

$source = Source::get("id={$definition->sourceId}");

if (!$refreshButton && !$acceptButton && !$moveButton) {
  // If a button was pressed, then this is a POST request and the URL
  // does not contain the definition ID.
  RecentLink::createOrUpdate(sprintf("Definiție: %s (%s)", $definition->lexicon, $source->shortName));
}

smarty_assign('def', $definition);
smarty_assign('source', $source);
smarty_assign('user', User::get("id = {$definition->userId}"));
smarty_assign('comment', $comment);
smarty_assign('lexems', $lexems);
smarty_assign('typos', db_find(new Typo(), "definitionId = {$definition->id}"));
smarty_assign('homonyms', loadSetHomonyms($lexems));
smarty_assign("allStatuses", util_getAllStatuses());
smarty_assign("allModeratorSources", db_find(new Source(), 'canModerate order by displayOrder'));
smarty_assign('recentLinks', RecentLink::loadForUser());
smarty_displayWithoutSkin('admin/definitionEdit.ihtml');

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
    $names[] = "'{$l->formNoAccent}'";
    $ids[] = "'{$l->id}'";
  }
  return db_find(new Lexem(), sprintf("formNoAccent in (%s) and id not in (%s)", join(',', $names), join(',', $ids)));
}

?>

<?
require_once("../../phplib/util.php"); 
util_assertModeratorStatus();
util_assertNotMirror();

$definitionId = util_getRequestIntParameter('definitionId');
$lexemNames = util_getRequestParameter('lexemName');
$lexemIds = util_getRequestParameter('lexemId');
$associateLexemId = util_getRequestParameter('associateLexemId');
$sourceId = util_getRequestIntParameter('source');
$internalRep = util_getRequestParameter('internalRep');
$status = util_getRequestIntParameterWithDefault('status', null);
$commentContents = util_getRequestParameter('commentContents');
$refreshButton = util_getRequestParameter('but_refresh');
$acceptButton = util_getRequestParameter('but_accept');
$moveButton = util_getRequestParameter('but_move');
$errorMessage = '';

if (!$definitionId) {
  return;
}

$definition = Definition::load($definitionId);
$comment = Comment::get("definitionId = '{$definitionId}' and status = " . ST_ACTIVE);
$oldInternalRep = $definition->internalRep;

if ($associateLexemId) {
  LexemDefinitionMap::associate($associateLexemId, $definitionId);
  util_redirect("definitionEdit.php?definitionId={$definitionId}");
}

if ($internalRep) {
  $definition->internalRep = text_internalizeDefinition($internalRep);
  $definition->htmlRep = text_htmlize($definition->internalRep);
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
  $ldms = array();
  for ($i = 0; $i < count($lexemNames); $i++) {
    $lexemName = trim($lexemNames[$i]);
    if ($lexemName) {
      $matches = Lexem::loadByExtendedName($lexemName);
      if (count($matches) == 1) {
        $lexems[] = $matches[0];
        $ldms[] = LexemDefinitionMap::create($matches[0]->id, $definitionId);
      } else {
        // If ambiguous, and if we have a corresponding lexemId, try to use it
        $lexemId = $lexemIds[$i];
        $found = false;
        for ($j = 0; $j < count($matches); $j++) {
          if ($matches[$j]->id == $lexemId) {
            $found = true;
            $lexems[] = $matches[$j];
            $ldms[] = LexemDefinitionMap::create($lexemId, $definitionId);
          }
        }
        if (!$found) {
          $errorMessage = (count($matches) == 0)
            ? "Lexemul <i>".htmlentities($lexemName)."</i> nu există. Folosiți " .
            "lista de sugestii pentru a-l corecta."
            : "Lexemul <i>".htmlentities($lexemName)."</i> este ambiguu. Folosiți " .
            "lista de sugestii pentru dezambiguizare.";
          $lexems[] = Lexem::create($lexemName, 0, '', '');
          // We won't be needing $ldms since there is an error.
        }
      }
    }
  }
} else {
  $lexems = Lexem::loadByDefinitionId($definitionId);
}

if ($commentContents) {
  if (!$comment) {
    $comment = new Comment();
    $comment->definitionId = $definitionId;
  }
  if ($commentContents != $comment->contents) {
    $comment->userId = session_getUserId();
    $comment->contents = text_internalizeDefinition($commentContents);
    $comment->htmlContents = text_htmlize($comment->contents);
  }
} else if ($comment) {
  // User wiped out the existing comment, set status to DELETED.
  $comment->status = ST_DELETED;
  $comment->userId = session_getUserId();  
}

if ($acceptButton || $moveButton) {
  if (!$errorMessage) {
    // The only difference between these two is that but_move also changes the
    // status to Active
    if ($moveButton) {
      $definition->status = ST_ACTIVE;
    }
    
    // Accept the definition and delete the typos associated with it.
    $definition->save();
    Typo::deleteAllByDefinitionId($definition->id);
    if ($comment) {
      $comment->save();
    }

    if ($definition->status == ST_DELETED) {
      // If by deleting this definition, any associated lexems become
      // unassociated, delete them
      $ldms = LexemDefinitionMap::loadByDefinitionId($definition->id);
      LexemDefinitionMap::deleteByDefinitionId($definition->id);

      foreach ($ldms as $ldm) {
        $l = Lexem::load($ldm->lexemId);
        $otherLdms = LexemDefinitionMap::loadByLexemId($l->id);
        if (!$l->isLoc && !count($otherLdms)) {
          $l->delete();
        }
      }
    } else {
      LexemDefinitionMap::deleteByDefinitionId($definitionId);
      foreach ($ldms as $ldm) {
        $ldm->save();
      }
    }
    
    log_userLog("Edited definition {$definition->id} ({$definition->lexicon})");
    util_redirect('definitionEdit.php?definitionId=' . $definitionId);
  } else {
    smarty_assign('errorMessage', $errorMessage);
  }
}

$source = Source::get("id={$definition->sourceId}");

if (!$refreshButton && !$acceptButton && !$moveButton) {
  // If a button was pressed, then this is a POST request and the URL
  // does not contain the definition ID.
  RecentLink::createOrUpdate(sprintf("Definiție: %s (%s)",
                                     $definition->lexicon,
                                     $source->shortName));
}

smarty_assign('def', $definition);
smarty_assign('source', $source);
smarty_assign('user', User::get("id = {$definition->userId}"));
smarty_assign('comment', $comment);
smarty_assign('lexems', $lexems);
smarty_assign('typos', Typo::loadByDefinitionId($definition->id));
smarty_assign('homonyms', Lexem::loadSetHomonyms($lexems));
smarty_assign("allStatuses", util_getAllStatuses());
smarty_assign("allModeratorSources", Source::findAll('canModerate'));
smarty_assign('recentLinks', RecentLink::loadForUser());
smarty_displayWithoutSkin('admin/definitionEdit.ihtml');

?>

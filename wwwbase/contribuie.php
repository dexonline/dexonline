<?php
require_once("../phplib/util.php");
util_assertNotMirror();

$lexemNames = util_getRequestParameter('lexemNames');
$sourceId = util_getRequestParameter('source');
$def = util_getRequestParameter('def');
$sendButton = util_getRequestParameter('send');

if ($sendButton) {
  session_setSourceCookie($sourceId);
  $ambiguousMatches = array();
  $def = text_internalizeDefinition($def, $sourceId, $ambiguousMatches);
  $lexemNames = deleteEmptyElements($lexemNames);

  $errorMessage = '';
  if (!count($lexemNames)) {
    $errorMessage = 'Trebuie să introduceți un cuvânt-titlu.';
  } else if (!$def) {
    $errorMessage = 'Trebuie să introduceți o definiție.';
  }

  if ($errorMessage) {
    smarty_assign('lexemNames', $lexemNames);
    smarty_assign('sourceId', $sourceId);
    smarty_assign('def', $def);
    session_setFlash($errorMessage);
    smarty_assign('previewDivContent', text_htmlize($def, $sourceId));
  } else {
    $definition = new Definition();
    $definition->userId = session_getUserId();
    $definition->sourceId = $sourceId;
    $definition->internalRep = $def;
    $definition->htmlRep = text_htmlize($def, $sourceId);
    $definition->lexicon = text_extractLexicon($definition);
    $definition->abbrevReview = count($ambiguousMatches) ? ABBREV_AMBIGUOUS : ABBREV_REVIEW_COMPLETE;
    $definition->save();
    log_userLog("Added definition {$definition->id} ({$definition->lexicon})");

    $ldms = array();
    foreach ($lexemNames as $lexemName) {
      $lexemName = addslashes(text_formatLexem($lexemName));
      if ($lexemName) {
        $matches = Lexem::loadByExtendedName($lexemName);
        if (count($matches) >= 1) {
          foreach ($matches as $match) {
            LexemDefinitionMap::associate($match->id, $definition->id);
            log_userLog("Associating with lexem {$match->id} ({$match->form})");
          }
        } else {
          // Create a new lexem.
          $lexem = new Lexem($lexemName, 'T', '1', '');
          $lexem->save();
          $lexem->regenerateParadigm();
          LexemDefinitionMap::associate($lexem->id, $definition->id);
          log_userLog("Created lexem {$lexem->id} ({$lexem->form})");
        }
      }
    }
    session_setFlash('Definiția a fost trimisă. Un moderator o va examina în scurt timp. Vă mulțumim!', 'info');
    util_redirect('contribuie');
  }
} else {
  smarty_assign('sourceId', session_getDefaultContribSourceId());
}

smarty_assign('contribSources', db_find(new Source(), 'canContribute order by displayOrder'));
smarty_assign('page_title', 'Contribuie cu definiții');
smarty_assign('suggestNoBanner', true);
smarty_displayCommonPageWithSkin('contribuie.ihtml');

/**************************************************************************/

function deleteEmptyElements(&$v) {
  $result = array();
  foreach ($v as $elem) {
    if (!empty($elem)) {
      $result[] = $elem;
    }
  }
  return $result;
}

?>

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
  $def = AdminStringUtil::internalizeDefinition($def, $sourceId, $ambiguousMatches);
  $lexemNames = deleteEmptyElements($lexemNames);

  $errorMessage = '';
  if (!count($lexemNames)) {
    $errorMessage = 'Trebuie să introduceți un cuvânt-titlu.';
  } else if (!$def) {
    $errorMessage = 'Trebuie să introduceți o definiție.';
  } else if (StringUtil::isSpam($def)) {
    $errorMessage = 'Definiția dumneavoastră este spam.';    
  }

  if ($errorMessage) {
    smarty_assign('lexemNames', $lexemNames);
    smarty_assign('sourceId', $sourceId);
    smarty_assign('def', $def);
    FlashMessage::add($errorMessage);
    smarty_assign('previewDivContent', AdminStringUtil::htmlize($def, $sourceId));
  } else {
    $definition = Model::factory('Definition')->create();
    $definition->displayed = 0;
    $definition->status = ST_PENDING;
    $definition->userId = session_getUserId();
    $definition->sourceId = $sourceId;
    $definition->internalRep = $def;
    $definition->htmlRep = AdminStringUtil::htmlize($def, $sourceId);
    $definition->lexicon = AdminStringUtil::extractLexicon($definition);
    $definition->abbrevReview = count($ambiguousMatches) ? ABBREV_AMBIGUOUS : ABBREV_REVIEW_COMPLETE;
    $definition->save();
    log_userLog("Added definition {$definition->id} ({$definition->lexicon})");

    $ldms = array();
    foreach ($lexemNames as $lexemName) {
      $lexemName = addslashes(AdminStringUtil::formatLexem($lexemName));
      if ($lexemName) {
        $matches = Lexem::loadByExtendedName($lexemName);
        if (count($matches) >= 1) {
          foreach ($matches as $match) {
            LexemDefinitionMap::associate($match->id, $definition->id);
            log_userLog("Associating with lexem {$match->id} ({$match->form})");
          }
        } else {
          // Create a new lexem.
          $lexem = Lexem::create($lexemName, 'T', '1', '');
          $lexem->save();
          $lexem->regenerateParadigm();
          LexemDefinitionMap::associate($lexem->id, $definition->id);
          log_userLog("Created lexem {$lexem->id} ({$lexem->form})");
        }
      }
    }
    FlashMessage::add('Definiția a fost trimisă. Un moderator o va examina în scurt timp. Vă mulțumim!', 'info');
    util_redirect('contribuie');
  }
} else {
  smarty_assign('sourceId', session_getDefaultContribSourceId());
}

smarty_assign('contribSources', Model::factory('Source')->where('canContribute', true)->order_by_asc('displayOrder')->find_many());
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

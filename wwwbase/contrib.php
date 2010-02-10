<?php
require_once("../phplib/util.php");
util_assertNotMirror();

$name = util_getRequestParameter('wordName');
$sourceId = util_getRequestParameter('source');
$def = util_getRequestParameter('def');
$sendButton = util_getRequestParameter('send');

if ($sendButton) {
  session_setSourceCookie($sourceId);

  $def = text_internalizeDefinition($def);

  $errorMessage = '';
  if (!$name) {
    $errorMessage = 'Trebuie să introduceți un cuvânt-titlu.';
  } else if (!$def) {
    $errorMessage = 'Trebuie să introduceți o definiție.';
  }

  if ($errorMessage) {
    smarty_assign('wordName', $name);
    smarty_assign('sourceId', $sourceId);
    smarty_assign('def', $def);
    smarty_assign('errorMessage', $errorMessage);
    smarty_assign('previewDivContent', text_htmlize($def));
  } else {
    $definition = new Definition();
    $definition->userId = session_getUserId();
    $definition->sourceId = $sourceId;
    $definition->internalRep = $def;
    $definition->htmlRep = text_htmlize($def);
    $definition->lexicon = text_extractLexicon($definition);
    $definition->save();
    $definition->id = db_getLastInsertedId();
    log_userLog("Added definition {$definition->id} ({$definition->lexicon})");

    $name = text_formatLexem($name);
    $lexems = Lexem::loadByForm($name);
    if (!count($lexems)) {
      $lexems = Lexem::loadByUnaccented($name);
    }
    if (count($lexems)) {
      // Reuse existing lexem.
      $lexem = $lexems[0];
      log_userLog("Reusing lexem {$lexem->id} ({$lexem->form})");
    } else {
      // Create a new lexem.
      $lexem = Lexem::create($name, 'T', '1', '');
      $lexem->save();
      $lexem->id = db_getLastInsertedId();
      $lexem->regenerateParadigm();
      log_userLog("Created lexem {$lexem->id} ({$lexem->form})");
    }

    LexemDefinitionMap::associate($lexem->id, $definition->id);

    smarty_assign('submissionSuccessful', 1);
    smarty_assign('sourceId', session_getDefaultContribSourceId());
  }
} else {
  smarty_assign('sourceId', session_getDefaultContribSourceId());
}

smarty_assign('contribSources', Source::loadAllContribSources());
smarty_displayWithoutSkin('common/contrib.ihtml');

?>

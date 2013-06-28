<?php
require_once("../phplib/util.php");
util_assertNotMirror();

$lexemIds = util_getRequestCsv('lexemIds');
$sourceId = util_getRequestParameter('source');
$def = util_getRequestParameter('def');
$sendButton = util_getRequestParameter('send');

if ($sendButton) {
  session_setSourceCookie($sourceId);
  $ambiguousMatches = array();
  $def = AdminStringUtil::internalizeDefinition($def, $sourceId, $ambiguousMatches);

  $errorMessage = '';
  if (!count($lexemIds)) {
    $errorMessage = 'Trebuie să introduceți un cuvânt-titlu.';
  } else if (!$def) {
    $errorMessage = 'Trebuie să introduceți o definiție.';
  } else if (StringUtil::isSpam($def)) {
    $errorMessage = 'Definiția dumneavoastră este spam.';    
  }

  if ($errorMessage) {
    SmartyWrap::assign('sourceId', $sourceId);
    SmartyWrap::assign('def', $def);
    FlashMessage::add($errorMessage);
    SmartyWrap::assign('previewDivContent', AdminStringUtil::htmlize($def, $sourceId));
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

    foreach ($lexemIds as $lexemId) {
      $lexemId = addslashes(AdminStringUtil::formatLexem($lexemId));
      if (StringUtil::startsWith($lexemId, '@')) {
        // create a new lexem
        $lexem = Lexem::create(substr($lexemId, 1), 'T', '1', '');
        $lexem->save();
        $lexem->regenerateParadigm();
        LexemDefinitionMap::associate($lexem->id, $definition->id);
        log_userLog("Created lexem {$lexem->id} ({$lexem->form})");
      } else {
        $lexem = Lexem::get_by_id($lexemId);
        LexemDefinitionMap::associate($lexem->id, $definition->id);
        log_userLog("Associating with lexem {$lexem->id} ({$lexem->form})");
      }
    }
    FlashMessage::add('Definiția a fost trimisă. Un moderator o va examina în scurt timp. Vă mulțumim!', 'info');
    util_redirect('contribuie');
  }
} else {
  SmartyWrap::assign('sourceId', session_getDefaultContribSourceId());
}

SmartyWrap::assign('lexemIds', $lexemIds);
SmartyWrap::assign('contribSources', Model::factory('Source')->where('canContribute', true)->order_by_asc('displayOrder')->find_many());
SmartyWrap::assign('page_title', 'Contribuie cu definiții');
SmartyWrap::assign('suggestNoBanner', true);
SmartyWrap::addCss('jqueryui', 'select2');
SmartyWrap::addJs('jqueryui', 'struct', 'select2');
SmartyWrap::displayCommonPageWithSkin('contribuie.ihtml');

/**************************************************************************/

?>

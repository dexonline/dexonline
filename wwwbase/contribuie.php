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

  if (!count($lexemIds)) {
    FlashMessage::add('Trebuie să introduceți un cuvânt-titlu.');
  } else if (!$def) {
    FlashMessage::add('Trebuie să introduceți o definiție.');
  } else if (StringUtil::isSpam($def)) {
    FlashMessage::add('Definiția dumneavoastră este spam.');
  }

  if (FlashMessage::hasMessages()) {
    SmartyWrap::assign('sourceId', $sourceId);
    SmartyWrap::assign('def', $def);
    SmartyWrap::assign('previewDivContent', AdminStringUtil::htmlize($def, $sourceId));
  } else {
    $definition = Model::factory('Definition')->create();
    $definition->status = Definition::ST_PENDING;
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
        $lexem = Lexem::deepCreate(substr($lexemId, 1), 'T', '1');
        $lexem->deepSave();
        LexemDefinitionMap::associate($lexem->id, $definition->id);
        log_userLog("Created lexem {$lexem->id} ({$lexem->form})");
      } else {
        $lexem = Lexem::get_by_id($lexemId);
        LexemDefinitionMap::associate($lexem->id, $definition->id);
        log_userLog("Associating with lexem {$lexem->id} ({$lexem->form})");
      }
    }
    FlashMessage::add('Am salvat definiția. Un moderator o va examina în scurt timp. Vă mulțumim!', 'success');
    util_redirect('contribuie');
  }
} else {
  SmartyWrap::assign('sourceId', session_getDefaultContribSourceId());
}

SmartyWrap::assign('lexemIds', $lexemIds);
SmartyWrap::assign('contribSources', Model::factory('Source')->where('canContribute', true)->order_by_asc('displayOrder')->find_many());
SmartyWrap::assign('suggestHiddenSearchForm', true);
SmartyWrap::assign('suggestNoBanner', true);
SmartyWrap::addCss('jqueryui', 'select2');
SmartyWrap::addJs('jqueryui', 'select2', 'select2Dev');
SmartyWrap::display('contribuie.tpl');

/**************************************************************************/

?>

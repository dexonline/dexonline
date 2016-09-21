<?php
require_once("../phplib/util.php");
util_assertNotMirror();

$lexemIds = Request::get('lexemIds');
$sourceId = Request::get('source');
$def = Request::get('def');
$sendButton = Request::isset('send');

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

  if (FlashMessage::hasErrors()) {
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
    Log::notice("Added definition {$definition->id} ({$definition->lexicon})");

    foreach ($lexemIds as $lexemId) {
      $lexemId = addslashes(AdminStringUtil::formatLexem($lexemId));
      if (StringUtil::startsWith($lexemId, '@')) {
        // create a new lexem
        $lexem = Lexem::create(substr($lexemId, 1), 'T', '1');
        $entry = Entry::createAndSave($lexem->formNoAccent);
        $lexem->entryId = $entry->id;
        $lexem->deepSave();
        EntryDefinition::associate($entry->id, $definition->id);
        Log::notice("Created lexem {$lexem->id} ({$lexem->form}) for definition {$definition->id}");
      } else {
        $lexem = Lexem::get_by_id($lexemId);
        EntryDefinition::associate($lexem->entryId, $definition->id);
        Log::notice("Associating definition {$definition->id} with lexem {$lexem->id} ({$lexem->form})");
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
SmartyWrap::addCss('tinymce');
SmartyWrap::addJs('select2Dev', 'tinymce', 'cookie');
SmartyWrap::display('contribuie.tpl');

?>

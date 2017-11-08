<?php
require_once("../phplib/Core.php");
Util::assertNotMirror();

$lexemIds = Request::get('lexemIds');
$sourceId = Request::get('source');
$def = Request::get('def');
$sendButton = Request::has('send');

if ($sendButton) {
  Session::setSourceCookie($sourceId);
  $ambiguousMatches = array();
  $def = AdminStringUtil::sanitize($def, $sourceId, $ambiguousMatches);

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
    $definition->userId = User::getActiveId();
    $definition->sourceId = $sourceId;
    $definition->internalRep = $def;
    $definition->htmlRep = AdminStringUtil::htmlize($def, $sourceId);
    $definition->lexicon = AdminStringUtil::extractLexicon($definition);
    $definition->abbrevReview = count($ambiguousMatches)
                              ? Definition::ABBREV_AMBIGUOUS
                              : Definition::ABBREV_REVIEW_COMPLETE;
    $definition->save();
    Log::notice("Added definition {$definition->id} ({$definition->lexicon})");

    foreach ($lexemIds as $lexemId) {
      $lexemId = AdminStringUtil::formatLexem($lexemId);
      if (StringUtil::startsWith($lexemId, '@')) {
        // create a new lexem
        $lexem = Lexem::create(substr($lexemId, 1), 'T', '1');
        $lexem->deepSave();
        $entry = Entry::createAndSave($lexem);
        EntryLexem::associate($entry->id, $lexem->id);
        EntryDefinition::associate($entry->id, $definition->id);
        Log::notice("Created lexem {$lexem->id} ({$lexem->form}) for definition {$definition->id}");
      } else {
        $lexem = Lexem::get_by_id($lexemId);
        foreach ($lexem->getEntries() as $e) {
          EntryDefinition::associate($e->id, $definition->id);
        }
        Log::notice("Associating definition {$definition->id} with lexem {$lexem->id} ({$lexem->form})");
      }
    }
    FlashMessage::add('Am salvat definiția. Un moderator o va examina în scurt timp. Vă mulțumim!', 'success');
    Util::redirect('contribuie');
  }
} else {
  SmartyWrap::assign('sourceId', Session::getDefaultContribSourceId());
}

SmartyWrap::assign('lexemIds', $lexemIds);
SmartyWrap::assign('contribSources', Model::factory('Source')->where('canContribute', true)->order_by_asc('displayOrder')->find_many());
SmartyWrap::addCss('tinymce');
SmartyWrap::addJs('select2Dev', 'tinymce', 'cookie');
SmartyWrap::display('contribuie.tpl');

?>

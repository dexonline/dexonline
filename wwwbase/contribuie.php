<?php
require_once("../phplib/Core.php");
Util::assertNotMirror();

$lexemIds = Request::getArray('lexemIds');
$sourceId = Request::get('sourceId');
$internalRep = Request::get('internalRep');
$status = Request::get('status', Definition::ST_PENDING);
$sendButton = Request::has('send');

$d = Model::factory('Definition')->create();

if ($sendButton) {
  Session::setSourceCookie($sourceId);
  $ambiguousMatches = [];

  $d->status = $status;
  $d->userId = User::getActiveId();
  $d->sourceId = $sourceId;
  $d->internalRep = Str::sanitize($internalRep, $sourceId, $ambiguousMatches);
  $d->htmlRep = Str::htmlize($d->internalRep, $d->sourceId);
  $d->abbrevReview = count($ambiguousMatches)
                   ? Definition::ABBREV_AMBIGUOUS
                   : Definition::ABBREV_REVIEW_COMPLETE;
  $d->extractLexicon();

  if (!count($lexemIds)) {
    FlashMessage::add('Trebuie să introduceți un cuvânt-titlu.');
  } else if (!$d->internalRep) {
    FlashMessage::add('Trebuie să introduceți o definiție.');
  } else if (Str::isSpam($d->internalRep)) {
    FlashMessage::add('Definiția dumneavoastră este spam.');
  }

  if (FlashMessage::hasErrors()) {
    SmartyWrap::assign('lexemIds', $lexemIds);
  } else {
    $d->save();
    Log::notice("Added definition {$d->id} ({$d->lexicon})");

    foreach ($lexemIds as $lexemId) {
      if (Str::startsWith($lexemId, '@')) {
        // create a new lexem
        $lexem = Lexem::create(substr($lexemId, 1), 'T', '1');
        $lexem->deepSave();
        $entry = Entry::createAndSave($lexem);
        EntryLexem::associate($entry->id, $lexem->id);
        EntryDefinition::associate($entry->id, $d->id);
        Log::notice("Created lexem {$lexem->id} ({$lexem->form}) for definition {$d->id}");
      } else {
        $lexem = Lexem::get_by_id($lexemId);
        foreach ($lexem->getEntries() as $e) {
          EntryDefinition::associate($e->id, $d->id);
        }
        Log::notice("Associating definition {$d->id} with lexem {$lexem->id} ({$lexem->form})");
      }
    }

    foreach (Str::findRedundantLinks($d->internalRep) as $processedLink) {
      if ($processedLink["short_reason"] !== "nemodificat") {
        FlashMessage::add('Legătura de la "' . $processedLink["original_word"] . '" la "' . $processedLink["linked_lexem"] . '" este considerată redundantă. (Motiv: ' . $processedLink["reason"] . ')', 'warning');
      }
    }

    if ($d->status == Definition::ST_ACTIVE) {
      FlashMessage::add('Am salvat definiția și am activat-o.', 'success');
    } else {
      FlashMessage::add('Am salvat definiția. Un moderator o va examina în scurt timp. Vă mulțumim!',
                        'success');
    }

    Util::redirect('contribuie');
  }
} else {
  $d->sourceId = Session::getDefaultContribSourceId();
  $d->status = User::can(User::PRIV_EDIT) ? Definition::ST_ACTIVE : Definition::ST_PENDING;
}

$sourceClauses = User::can(User::PRIV_EDIT)
  ? [['canContribute' => true], ['canModerate' => true]]
  : [['canContribute' => true]];
$sources = Model::factory('Source')
         ->where_any_is($sourceClauses)
         ->order_by_desc('dropdownOrder')
         ->order_by_asc('displayOrder')
         ->find_many();

SmartyWrap::assign('d', $d);
SmartyWrap::assign('contribSources', $sources);
SmartyWrap::addCss('tinymce');
SmartyWrap::addJs('select2Dev', 'tinymce', 'cookie');
SmartyWrap::display('contribuie.tpl');

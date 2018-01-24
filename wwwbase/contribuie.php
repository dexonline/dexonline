<?php
require_once("../phplib/Core.php");
Util::assertNotMirror();

$lexemeIds = Request::getArray('lexemeIds');
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

  $errors = [];
  $d->internalRep = Str::sanitize($internalRep, $sourceId, $errors, $ambiguousMatches);
  foreach ($errors as $error) {
    FlashMessage::add($error);
  }

  $errors = [];
  $d->htmlRep = Str::htmlize($d->internalRep, $d->sourceId, $errors);
  foreach ($errors as $error) {
    FlashMessage::add($error);
  }

  $d->abbrevReview = count($ambiguousMatches)
                   ? Definition::ABBREV_AMBIGUOUS
                   : Definition::ABBREV_REVIEW_COMPLETE;
  $d->extractLexicon();

  if (!count($lexemeIds)) {
    FlashMessage::add('Trebuie să introduceți un cuvânt-titlu.');
  } else if (!$d->internalRep) {
    FlashMessage::add('Trebuie să introduceți o definiție.');
  } else if (Str::isSpam($d->internalRep)) {
    FlashMessage::add('Definiția dumneavoastră este spam.');
  }

  if (FlashMessage::hasErrors()) {
    SmartyWrap::assign('lexemeIds', $lexemeIds);
  } else {
    $d->save();
    Log::notice("Added definition {$d->id} ({$d->lexicon})");

    foreach ($lexemeIds as $lexemeId) {
      if (Str::startsWith($lexemeId, '@')) {
        // create a new lexem
        $lexeme = Lexeme::create(substr($lexemeId, 1), 'T', '1');
        $lexeme->deepSave();
        $entry = Entry::createAndSave($lexem);
        EntryLexeme::associate($entry->id, $lexeme->id);
        EntryDefinition::associate($entry->id, $d->id);
        Log::notice("Created lexeme {$lexeme->id} ({$lexeme->form}) for definition {$d->id}");
      } else {
        $lexeme = Lexeme::get_by_id($lexemeId);
        foreach ($lexeme->getEntries() as $e) {
          EntryDefinition::associate($e->id, $d->id);
        }
        Log::notice("Associating definition {$d->id} with lexeme {$lexeme->id} ({$lexeme->form})");
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

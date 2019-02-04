<?php
require_once '../../lib/Core.php';
User::mustHave(User::PRIV_EDIT | User::PRIV_TRAINEE);

$definitionId = Request::get('definitionId');
$isOcr = Request::get('isOcr');
$entryIds = Request::getArray('entryIds');
$sourceId = Request::get('source');
$structured = Request::has('structured');
$internalRep = Request::get('internalRep');
$status = Request::get('status', null);
$tagIds = Request::getArray('tagIds');
$volume = Request::get('volume', 0);
$page = Request::get('page', 0);

$saveButton = Request::has('saveButton');
$nextOcrBut = Request::has('but_next_ocr');

$userId = User::getActiveId();

if ($isOcr && !$definitionId) {
  // user requested an OCR definition
  getDefinitionFromOcr($userId);
} else if (!$definitionId) {
  // create a new definition
  $d = Model::factory('Definition')->create();
  $d->status = getDefaultStatus();
  $d->userId = $userId;

  $d->sourceId = Session::getSourceCookie();
  if (!$d->sourceId) {
    $s = Model::factory('Source')
       ->where('canModerate', true)
       ->order_by_asc('displayOrder')
       ->find_one();
    $d->sourceId = $s->id;
  }
} else {
  $d = Definition::get_by_id($definitionId);
  if (!$d) {
    FlashMessage::add("Nu există nicio definiție cu ID-ul {$definitionId}.");
    Util::redirect('index.php');
  }
  if (User::isTrainee() && $userId != $d->userId) {
    FlashMessage::add("Nu aveți suficiente drepturi pentru a accesa definiția cu ID-ul {$definitionId}.");
    Util::redirect('index.php');
  }
}

if ($saveButton || $nextOcrBut) {
  $d->internalRep = $internalRep;
  $d->status = (int)$status;
  $d->sourceId = (int)$sourceId;
  $d->structured = $structured;
  $d->volume = $volume;
  $d->page = $page;

  $d->process(true);
  $d->setVolumeAndPage(); // only after we have extracted the lexicon
  $d->updateRareGlyphs();

  HtmlConverter::convert($d);
  HtmlConverter::exportMessages();

  checkDeletedStructuredEntries($d, $entryIds);
  checkRareGlyphsFieldAndTag($d, $tagIds);

  if (!FlashMessage::hasErrors()) {
    // Save the new entries, load the rest.
    $noAccentNag = false;
    $entries = [];
    foreach ($entryIds as $entryId) {
      if (Str::startsWith($entryId, '@')) {
        // create a new lexeme and entry
        $form = substr($entryId, 1);
        $l = Lexeme::create($form, 'T', '1');
        $e = Entry::createAndSave($l, true);
        $l->save();
        $l->regenerateParadigm();
        EntryLexeme::associate($e->id, $l->id);

        if (strpos($form, "'") === false) {
          $noAccentNag = true;
        }

      } else {
        $e = Entry::get_by_id($entryId);
      }
      $entries[] = $e;
    }
    if ($noAccentNag) {
      FlashMessage::add('Vă rugăm să indicați accentul pentru lexemele noi oricând se poate.',
                        'warning');
    }

    if (User::isTrainee()
        && !TraineeSource::TraineeCanEditSource($userId, $d->sourceId)
        && $d->status == Definition::ST_ACTIVE) {
      $d->status = Definition::ST_PENDING;
      FlashMessage::add('Am trecut definiția înapoi în starea temporară, ' .
                        'iar un moderator o va examina curând.', 'warning');
    }

    // Save the definition and delete the typos associated with it.
    $d->save();

    $orig = Definition::get_by_id($definitionId);
    if ($d->structured && $orig && ($d->internalRep != $orig->internalRep)) {
      FlashMessage::add('Ați modificat o definiție deja structurată. Dacă se poate, ' .
                        'vă rugăm să modificați corespunzător și arborele de sensuri.',
                        'warning');
    }
    if (!$d->lexicon) {
      FlashMessage::add('Câmpul lexicon este vid. Aceasta se întâmplă de obicei când omiteți ' .
                        'să încadrați cuvântul-titlu între @...@.',
                        'warning');
    }

    if ($d->status == Definition::ST_DELETED) {
      EntryDefinition::dissociateDefinition($d->id);
    } else {
      EntryDefinition::update(Util::objectProperty($entries, 'id'), $d->id);
    }

    ObjectTag::wipeAndRecreate($d->id, ObjectTag::TYPE_DEFINITION, $tagIds);

    Log::notice("Saved definition {$d->id} ({$d->lexicon})");

    Session::setSourceCookie($d->sourceId);

    if ($nextOcrBut) {
      // cause the next OCR definition to load
      Util::redirect('definitionEdit.php?isOcr=1');
    } else {
      $url = "definitionEdit.php?definitionId={$d->id}";
      if ($isOcr) {
        // carry this around so the user can click "Save" any number of times, then "next OCR".
        $url .= "&isOcr=1";
      }
      Util::redirect($url);
    }
  } else {
    // There were errors saving.
  }
} else {
  // First time loading this page -- not a save.
  if ($d->id) {
    RecentLink::add(sprintf('Definiție: %s (%s) (ID=%s)',
                            $d->lexicon, $d->getSource()->shortName, $d->id));
  }

  $entries = $d->getEntries();
  $entryIds = Util::objectProperty($entries, 'id');

  $dts = ObjectTag::getDefinitionTags($d->id);
  $tagIds = Util::objectProperty($dts, 'tagId');
}

$typos = Model::factory('Typo')
  ->where('definitionId', $d->id)
  ->order_by_asc('id')
  ->find_many();

if ($isOcr && empty($entryIds)) {
  $d->extractLexicon();
  if ($d->lexicon) {
    $entries = Model::factory('Definition')
      ->table_alias('d')
      ->select('ed.entryId')
      ->distinct()
      ->join('EntryDefinition', ['d.id', '=', 'ed.definitionId'], 'ed')
      ->where('d.lexicon', $d->lexicon)
      ->limit(10)
      ->find_many();
    $entryIds = Util::objectProperty($entries, 'entryId');
  }
}

// If we got here, either there were errors saving, or this is the first time
// loading the page.

// create a stub SearchResult so we can show the menu
$row = new SearchResult();
$row->definition = $d;
$row->source = $d->getSource();

$sources = Model::factory('Source')
         ->where('canModerate', true)
         ->order_by_asc('displayOrder')
         ->find_many();

SmartyWrap::assign([
  'isOcr' => $isOcr,
  'def' => $d,
  'row' => $row,
  'source' => $d->getSource(),
  'sim' => SimilarRecord::create($d, $entryIds),
  'user' => User::get_by_id($d->userId),
  'entryIds' => $entryIds,
  'tagIds' => $tagIds,
  'typos' => $typos,
  'canEdit' => canEdit($d),
  'canEditStatus' => canEditStatus($d),
  'allModeratorSources' => $sources,
]);
SmartyWrap::addCss('tinymce', 'admin', 'diff');
SmartyWrap::addJs('select2Dev', 'tinymce', 'cookie', 'frequentObjects');
SmartyWrap::display('admin/definitionEdit.tpl');

/*************************************************************************/

// loads an OCR definition assigned to this user and redirects to it
function getDefinitionFromOcr($userId) {
  checkPendingLimit($userId);

  // try to load a definition from the OCR queue
  $ocr = OCR::getNext($userId);
  if (!$ocr) {
    FlashMessage::add('Lista cu definiții OCR este goală.', 'warning');
    Util::redirect('index.php');
  }

  // Found one, create the Definition and update the OCR.
  $d = Model::factory('Definition')->create();
  $d->status = getDefaultStatus();
  $d->userId = $userId;
  $d->sourceId = $ocr->sourceId;
  $d->structured = 0;
  $d->internalRep = $ocr->ocrText;
  $d->process();
  $d->save();
  // do not set the volume and page here as the lexicon could be completely wrong

  $ocr->definitionId = $d->id;
  $ocr->editorId = $userId;
  $ocr->status = 'published';
  $ocr->save();

  Log::notice("Imported definition {$d->id} ({$d->lexicon}) from OCR {$ocr->id}");

  Util::redirect("definitionEdit.php?definitionId={$d->id}&isOcr=1");
}

// check the pending definitions limit for trainees
function checkPendingLimit($userId) {
  if (User::isTrainee()) {
    $pending = Model::factory('Definition')
             ->where('userId', $userId)
             ->where('status', Definition::ST_PENDING)
             ->count();
    $limit = Config::LIMIT_TRAINEE_PENDING_DEFS;
    if ($pending >= $limit) {
      FlashMessage::add("Ați atins limita de {$limit} definiții nemoderate.");
      Util::redirect('index.php');
    }
  }
}

// only structurists are allowed to dissociate structured entries
function checkDeletedStructuredEntries($d, $entryIds) {

  if (User::can(User::PRIV_STRUCT)) {
    return; // no limitations for structurists
  }

  if (empty($entryIds)) {
    return; // no associated entries -> prevent where_in barf
  }

  // collect dissociated structured entries
  $entries = Model::factory('Entry')
    ->table_alias('e')
    ->select('e.*')
    ->join('EntryDefinition', ['e.id', '=', 'ed.entryId'], 'ed')
    ->where('ed.definitionId', $d->id)
    ->where_in('e.structStatus', [Entry::STRUCT_STATUS_UNDER_REVIEW, Entry::STRUCT_STATUS_DONE])
    ->where_not_in('e.id', $entryIds)
    ->find_many();

  if (count($entries)) {
    FlashMessage::addTemplate('dissociateStructuredEntries.tpl', ['entries' => $entries]);
  }
}

// if the definition has rare glyphs, then either
// (1) it should also have a [rare glyphs] tag or
// (2) it should be saved in ST_PENDING
function checkRareGlyphsFieldAndTag($d, &$tagIds) {
  $tag = Tag::get_by_id(Config::TAG_ID_RARE_GLYPHS);
  $hasTag = in_array($tag->id, $tagIds);

  // Rare glyphs, active state and no tag? Complain!
  if ($d->rareGlyphs && !$hasTag && $d->status != Definition::ST_PENDING) {
    FlashMessage::addTemplate('rareGlyphsNoTag.tpl', [
      'definition' => $d,
      'rareGlyphsTag' => $tag,
    ]);
  }

  // No rare glyphs, but tag present? Remove tag.
  if (!$d->rareGlyphs && $hasTag) {
    $pos = array_search($tag->id, $tagIds);
    unset($tagIds[$pos]);
    FlashMessage::addTemplate('rareGlyphsUnneededTag.tpl',
                              [ 'rareGlyphsTag' => $tag ],
                              'warning');
  }
}

function getDefaultStatus() {
  return User::can(User::PRIV_EDIT) ? Definition::ST_ACTIVE : Definition::ST_PENDING;
}

// trainees cannot edit the status field
function canEditStatus($definition) {
  return !User::isTrainee() ||
    TraineeSource::TraineeCanEditSource($definition->userId,$definition->sourceId);
}

// trainees can only edit their own definitions
function canEdit($definition) {
  return !User::isTrainee() ||
    ($definition->userId == User::getActiveId());
}

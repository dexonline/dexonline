<?php
require_once("../../phplib/util.php"); 
util_assertModerator(PRIV_EDIT);
util_assertNotMirror();

$definitionId = util_getRequestIntParameter('definitionId');

if (!$definitionId) {
  // User requested an OCR definition. Try to find one.
  $ocr = OCR::getNext(session_getUserId());
  if (!$ocr) {
    echo('Lista cu definiții OCR este goală.');
    return;
  }

  // Found one, create the Definition and update the OCR.
  $ambiguousMatches = [];
  $sourceId = $ocr->sourceId;
  $def = AdminStringUtil::internalizeDefinition($ocr->ocrText, $sourceId, $ambiguousMatches);

  $d = Model::factory('Definition')->create();
  $d->status = Definition::ST_ACTIVE;
  $d->userId = session_getUserId();
  $d->sourceId = $sourceId;
  $d->similarSource = 0;
  $d->structured = 0;
  $d->internalRep = $def;
  $d->htmlRep = AdminStringUtil::htmlize($def, $sourceId);
  $d->lexicon = AdminStringUtil::extractLexicon($d);
  $d->abbrevReview = count($ambiguousMatches) ? ABBREV_AMBIGUOUS : ABBREV_REVIEW_COMPLETE;
  $d->save();

  $ocr->definitionId = $d->id;
  $ocr->editorId = session_getUserId();
  $ocr->status = 'published';
  $ocr->save();

  Log::notice("Imported definition {$d->id} ({$d->lexicon}) from OCR {$ocr->id}");

  // Redirect to the new Definition.
  util_redirect("definitionEdit.php?definitionId={$d->id}&isOCR=1");
}

if (!($d = Definition::get_by_id($definitionId))) {
  FlashMessage::add("Nu există nicio definiție cu ID-ul {$definitionId}.");
  util_redirect("index.php");
}

// Load request fields and buttons.
$isOCR = util_getRequestParameter('isOCR');
$entryIds = util_getRequestParameter('entryIds');
$sourceId = util_getRequestIntParameter('source');
$similarSource = util_getBoolean('similarSource');
$structured = util_getBoolean('structured');
$internalRep = util_getRequestParameter('internalRep');
$status = util_getRequestIntParameterWithDefault('status', null);
$commentContents = util_getRequestParameter('commentContents');
$preserveCommentUser = util_getRequestParameter('preserveCommentUser');
$tagIds = util_getRequestParameterWithDefault('tagIds', []);

$saveButton = util_getBoolean('saveButton');
$nextOcrBut = util_getBoolean('but_next_ocr');

$comment = Model::factory('Comment')->where('definitionId', $d->id)->where('status', Definition::ST_ACTIVE)->find_one();
$commentUser = $comment ? User::get_by_id($comment->userId) : null;

if ($saveButton || $nextOcrBut) {
  $errors = [];
  $d->internalRep = AdminStringUtil::internalizeDefinition($internalRep, $sourceId);
  $d->htmlRep = AdminStringUtil::htmlize($d->internalRep, $sourceId, $errors);
  foreach ($errors as $error) {
    FlashMessage::add($error);
  }

  $d->status = (int)$status;
  $d->sourceId = (int)$sourceId;
  $d->similarSource = $similarSource;
  $d->structured = $structured;
  $d->lexicon = AdminStringUtil::extractLexicon($d);

  if ($commentContents) {
    if (!$comment) {
      // Comment added
      $comment = Model::factory('Comment')->create();
      $comment->status = Definition::ST_ACTIVE;
      $comment->definitionId = $d->id;
    }
    $newContents = AdminStringUtil::internalizeDefinition($commentContents, $sourceId);
    if ($newContents != $comment->contents) {
      // Comment updated
      $comment->contents = $newContents;
      $comment->htmlContents = AdminStringUtil::htmlize($comment->contents, $sourceId);
      if (!$preserveCommentUser) {
        $comment->userId = session_getUserId();
      }
    }
  } else if ($comment) {
    // User wiped out the existing comment, set status to DELETED.
    $comment->status = Definition::ST_DELETED;
    $comment->userId = session_getUserId();  
  }

  if (!FlashMessage::hasErrors()) {
    // Save the new entries, load the rest.
    $noAccentNag = false;
    $entries = [];
    foreach ($entryIds as $entryId) {
      if (StringUtil::startsWith($entryId, '@')) {
        // create a new lexem and entry
        $form = substr($entryId, 1);
        $l = Lexem::create($form, 'T', '1');
        $e = Entry::createAndSave($l->formNoAccent);
        $l->entryId = $e->id;
        $l->save();
        $l->regenerateParadigm();

        if (strpos($form, "'") === false) {
          $noAccentNag = true;
        }

      } else {
        $e = Entry::get_by_id($entryId);
      }
      $entries[] = $e;
    }
    if ($noAccentNag) {
      FlashMessage::add('Vă rugăm să indicați accentul pentru lexemele noi oricând se poate.', 'warning');
    }

    // Save the definition and delete the typos associated with it.
    $d->save();
    Typo::delete_all_by_definitionId($d->id);
    if ($comment) {
      $comment->save();
    }

    if ($d->status == Definition::ST_DELETED) {
      EntryDefinition::dissociateDefinition($d->id);
    } else {
      // Save the associations.
      EntryDefinition::delete_all_by_definitionId($d->id);
      foreach ($entries as $e) {
        EntryDefinition::associate($e->id, $d->id);
      }
    }
    
    // Delete the old tags and add the new tags.
    DefinitionTag::delete_all_by_definitionId($d->id);
    foreach ($tagIds as $tagId) {
      $dt = Model::factory('DefinitionTag')->create();
      $dt->definitionId = $d->id;
      $dt->tagId = $tagId;
      $dt->save();
    }

    Log::notice("Saved definition {$d->id} ({$d->lexicon})");
  
    if ($nextOcrBut) {
      // cause the next OCR definition to load
      util_redirect('definitionEdit.php');
    } else {
      $url = "definitionEdit.php?definitionId={$d->id}";
      if ($isOCR) {
        // carry this around so the user can click "Save" any number of times, then "next OCR".
        $url .= "&isOCR=1";
      }
      util_redirect($url);
    }
  } else {
    // There were errors saving.
  }
} else {
  // First time loading this page -- not a save.
  RecentLink::add(sprintf('Definiție: %s (%s) (ID=%s)',
                          $d->lexicon, $d->getSource()->shortName, $d->id));

  $entries = Model::factory('Entry')
           ->table_alias('e')
           ->select('e.id')
           ->join('EntryDefinition', ['ed.entryId', '=', 'e.id'], 'ed')
           ->where('ed.definitionId', $d->id)
           ->order_by_asc('description')
           ->find_many();
  $entryIds = util_objectProperty($entries, 'id');

  $dts = DefinitionTag::get_all_by_definitionId($d->id);
  $tagIds = util_objectProperty($dts, 'tagId');
}

$typos = Model::factory('Typo')
  ->where('definitionId', $d->id)
  ->order_by_asc('id')
  ->find_many();

// Either there were errors saving, or this is the first time loading the page.
SmartyWrap::assign('isOCR', $isOCR);
SmartyWrap::assign('def', $d);
SmartyWrap::assign('source', $d->getSource());
SmartyWrap::assign('sim', SimilarRecord::create($d, $entryIds));
SmartyWrap::assign('user', User::get_by_id($d->userId));
SmartyWrap::assign('comment', $comment);
SmartyWrap::assign('commentUser', $commentUser);
SmartyWrap::assign('entryIds', $entryIds);
SmartyWrap::assign('tagIds', $tagIds);
SmartyWrap::assign('typos', $typos);
SmartyWrap::assign("allModeratorSources", Model::factory('Source')->where('canModerate', true)->order_by_asc('displayOrder')->find_many());
SmartyWrap::addCss('select2', 'tinymce', 'admin');
SmartyWrap::addJs('select2', 'tinymce', 'cookie');
SmartyWrap::display('admin/definitionEdit.tpl');

?>

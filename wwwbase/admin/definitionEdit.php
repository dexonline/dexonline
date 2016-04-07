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
$lexemIds = util_getRequestCsv('lexemIds');
$sourceId = util_getRequestIntParameter('source');
$similarSource = util_getBoolean('similarSource');
$internalRep = util_getRequestParameter('internalRep');
$status = util_getRequestIntParameterWithDefault('status', null);
$commentContents = util_getRequestParameter('commentContents');
$preserveCommentUser = util_getRequestParameter('preserveCommentUser');

$acceptButton = util_getRequestParameter('but_accept');
$nextOcrBut = util_getRequestParameter('but_next_ocr');

$comment = Model::factory('Comment')->where('definitionId', $d->id)->where('status', Definition::ST_ACTIVE)->find_one();
$commentUser = $comment ? User::get_by_id($comment->userId) : null;

if ($acceptButton || $nextOcrBut) {
  $errors = [];
  $d->internalRep = AdminStringUtil::internalizeDefinition($internalRep, $sourceId);
  $d->htmlRep = AdminStringUtil::htmlize($d->internalRep, $sourceId, $errors);
  foreach ($errors as $error) {
    FlashMessage::add($error);
  }

  $d->status = (int)$status;
  $d->sourceId = (int)$sourceId;
  $d->similarSource = $similarSource;
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
    // Save the new lexems, load the rest.
    $lexems = [];
    foreach ($lexemIds as $lexemId) {
      if (StringUtil::startsWith($lexemId, '@')) {
        // create a new lexem
        $form = substr($lexemId, 1);
        $l = Lexem::deepCreate($form, 'T', '1');
        $l->deepSave();
        if (strpos($form, "'") === false) {
          FlashMessage::add('Vă rugăm să indicați accentul pentru lexemul nou oricând se poate.', 'warning');
        }
      } else {
        $l = Lexem::get_by_id($lexemId);
      }
      $lexems[] = $l;
    }

    // Save the definition and delete the typos associated with it.
    $d->save();
    db_execute("delete from Typo where definitionId = {$d->id}");
    if ($comment) {
      $comment->save();
    }

    if ($d->status == Definition::ST_DELETED) {
      // If by deleting this definition, any associated lexems become unassociated, delete them
      $ldms = LexemDefinitionMap::get_all_by_definitionId($d->id);
      db_execute("delete from LexemDefinitionMap where definitionId = {$d->id}");

      foreach ($ldms as $ldm) {
        $l = Lexem::get_by_id($ldm->lexemId);
        $otherLdms = LexemDefinitionMap::get_all_by_lexemId($l->id);
        if (!$l->isLoc() && !count($otherLdms)) {
          Log::warning("Deleting unassociated lexem {$l->id} ({$l->formNoAccent})");
          $l->delete();
        }
      }
    } else {
      // Save the associations.
      db_execute("delete from LexemDefinitionMap where definitionId = {$d->id}");
      foreach ($lexems as $l) {
        LexemDefinitionMap::associate($l->id, $d->id);
      }
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
  RecentLink::createOrUpdate(
    sprintf('Definiție: %s (%s)', $d->lexicon, $d->getSource()->shortName));

  $lexems = Model::factory('Lexem')
          ->select('Lexem.*')
          ->join('LexemDefinitionMap', 'Lexem.id = lexemId', 'ldm')
          ->where('ldm.definitionId', $d->id)
          ->order_by_asc('formNoAccent')
          ->find_many();
  $lexemIds = util_objectProperty($lexems, 'id');
}

// Either there were errors saving, or this is the first time loading the page.
SmartyWrap::assign('isOCR', $isOCR);
SmartyWrap::assign('def', $d);
SmartyWrap::assign('source', $d->getSource());
SmartyWrap::assign('sim', SimilarRecord::create($d, $lexemIds));
SmartyWrap::assign('user', User::get_by_id($d->userId));
SmartyWrap::assign('comment', $comment);
SmartyWrap::assign('commentUser', $commentUser);
SmartyWrap::assign('lexemIds', $lexemIds);
SmartyWrap::assign('typos', Typo::get_all_by_definitionId($d->id));
SmartyWrap::assign('homonyms', loadSetHomonyms($lexemIds));
SmartyWrap::assign("allModeratorSources", Model::factory('Source')->where('canModerate', true)->order_by_asc('displayOrder')->find_many());
SmartyWrap::assign('recentLinks', RecentLink::loadForUser());
SmartyWrap::addCss('jqueryui', 'select2');
SmartyWrap::addJs('jquery', 'jqueryui', 'select2', 'select2Dev', 'definitionEdit', 'tinymce', 'cookie');
SmartyWrap::displayAdminPage('admin/definitionEdit.tpl');

/**
 * Load all lexems having the same form as one of the given lexems, but exclude the given lexems.
 **/
function loadSetHomonyms($lexemIds) {
  if (count($lexemIds) == 0) {
    return array();
  }

  $lexems = [];
  foreach ($lexemIds as $id) {
    if (!StringUtil::startsWith($id, '@')) {
      $lexems[] = Lexem::get_by_id($id);
    }
  }

  $names = util_objectProperty($lexems, 'formNoAccent');
  $ids = util_objectProperty($lexems, 'id');
  return Model::factory('Lexem')->where_in('formNoAccent', $names)->where_not_in('id', $ids)->find_many();
}

?>

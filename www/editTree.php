<?php
require_once '../phplib/Core.php';

User::mustHave(User::PRIV_EDIT | User::PRIV_STRUCT);

$id = Request::get('id');
$saveButton = Request::has('saveButton');
$mergeButton = Request::has('mergeButton');
$cloneButton = Request::has('cloneButton');
$delete = Request::has('delete');

if ($id) {
  $t = Tree::get_by_id($id);
  if (!$t) {
    FlashMessage::add('Arborele nu există.');
    Util::redirectToHome();
  }
} else {
  $t = Model::factory('Tree')->create();
}

if ($mergeButton) {
  $mergeTreeId = Request::get('mergeTreeId');
  $other = Tree::get_by_id($mergeTreeId);

  if (!$other) {
    FlashMessage::add('Arborele selectat nu există.');
    Util::redirect("?id={$t->id}");
  }

  $treeMentions = Mention::getTreeMentions($t->id);
  if (count($treeMentions)) {
    FlashMessage::add('Nu puteți unifica acest arbore până nu rezolvați mențiunile despre el.');
    Util::redirect("?id={$t->id}");
  }

  $t->mergeInto($other->id);

  FlashMessage::add('Am unificat arborii.', 'success');
  Util::redirect("?id={$other->id}");
}

if ($cloneButton) {
  $newt = $t->_clone();
  Log::info("Cloned tree {$t->id} ({$t->description}), new id {$newt->id}");

  // Warn if the original tree had relations or meaning or tree mentions
  $triggers = count($t->getRelatedMeanings()) +
            count(Mention::getTreeMentions($t->id)) +
            count(Mention::getDetailedMeaningMentions($t->id));
  if ($triggers) {
    FlashMessage::add(
      'Am clonat arborele. Nu uitați că arborele original avea mențiuni și/sau relații.',
      'warning'
    );
  } else {
    FlashMessage::add('Am clonat arborele.', 'success');
  }

  Util::redirect("?id={$newt->id}");
}

if ($delete) {
  $te = TreeEntry::get_by_treeId($t->id); // try to redirect to a relevant entry
  $t->delete();
  FlashMessage::add('Am șters arborele.', 'success');

  if ($te) {
    Util::redirect("editEntry.php?id={$te->entryId}");
  } else {
    Util::redirectToHome();
  }
}

if ($saveButton) {
  $t->description = Request::get('description');
  $t->status = Request::get('status');
  $entryIds = Request::getArray('entryIds');
  $tagIds = Request::getArray('tagIds');
  $meanings = Request::getJson('jsonMeanings', []);

  $errors = $t->validate();
  if ($errors) {
    FlashMessage::add('Nu pot salva arborele datorită erorilor de mai jos.');
    SmartyWrap::assign('errors', $errors);
    // TODO fix logic here - individual meanings can give errors too (e.g. due to abbreviations)
    $t->setMeanings(Meaning::convertTree($meanings));
  } else {
    $t->save();

    TreeEntry::update($t->id, $entryIds);
    ObjectTag::wipeAndRecreate($t->id, ObjectTag::TYPE_TREE, $tagIds);

    Meaning::saveTree($meanings, $t);

    FlashMessage::add('Am salvat arborele.', 'success');

    Util::redirect("?id={$t->id}");
  }
} else {
  $t->getMeanings(); // ensure they are loaded
  $entryIds = $t->getEntryIds();
  $ots = ObjectTag::getTreeTags($t->id);
  $tagIds = Util::objectProperty($ots, 'tagId');
  RecentLink::add("Arbore: {$t->description} (ID={$t->id})");
}

// Load the distinct model types for the entries' lexemes
if (count($entryIds)) {
  $modelTypes = Model::factory('Lexeme')
              ->table_alias('l')
              ->select('l.modelType')
              ->distinct()
              ->join('EntryLexeme', ['el.lexemeId', '=', 'l.id'], 'el')
              ->where_in('el.entryId', $entryIds)
              ->order_by_asc('modelType')
              ->find_many();
} else {
  $modelTypes = [];
}

$relatedMeanings = $t->getRelatedMeanings();
foreach ($relatedMeanings as $m) {
  $m->getTree(); // preload it
}

// find other trees from this tree's entries
$entryTrees = Model::factory('Tree')
  ->table_alias('t')
  ->select('t.*')
  ->join('TreeEntry', ['t.id', '=', 'te.treeId'], 'te')
  ->join('TreeEntry', ['te.entryId', '=', 'te2.entryId'], 'te2')
  ->where('te2.treeId', $t->id)
  ->where_not_equal('t.id', $t->id)
  ->find_many();

$treeMentions = Mention::getDetailedTreeMentions($t->id);
$meaningMentions = Mention::getDetailedMeaningMentions($t->id);

$homonyms = $t->getRelatedTrees();

SmartyWrap::assign([
  't' => $t,
  'entryIds' => $entryIds,
  'tagIds' => $tagIds,
  'modelTypes' => $modelTypes,
  // TODO: canEdit if STRUCT_STATUS_IN_PROGRESS || User::can(User::PRIV_EDIT)
  'canEdit' => true,
  'canDelete' => $t->canDelete(),
  'relatedMeanings' => $relatedMeanings,
  'entryTrees' => $entryTrees,
  'treeMentions' => $treeMentions,
  'meaningMentions' => $meaningMentions,
  'homonyms' => $homonyms,
]);
SmartyWrap::addCss('editableMeaningTree', 'textComplete', 'admin');
SmartyWrap::addJs('select2Dev', 'meaningTree', 'textComplete', 'cookie', 'frequentObjects');
SmartyWrap::display('editTree.tpl');

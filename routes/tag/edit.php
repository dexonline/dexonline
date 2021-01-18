<?php
User::mustHave(User::PRIV_EDIT);

const DEF_LIMIT = 20;
const LEXEME_LIMIT = 100;
const MEANING_LIMIT = 50;

$id = Request::get('id');
$saveButton = Request::has('saveButton');
$deleteButton = Request::has('deleteButton');

if ($id) {
  $tag = Tag::get_by_id($id);
} else {
  $tag = Model::factory('Tag')->create();
  $tag->public = 1;
}

if ($saveButton) {
  $tag->value = Request::get('value');
  $tag->parentId = Request::get('parentId', 0);
  $tag->setColor(Request::get('color'));
  $tag->setBackground(Request::get('background'));
  $tag->icon = Request::get('icon');
  $tag->iconOnly = Request::has('iconOnly');
  $tag->tooltip = Request::get('tooltip');
  $tag->public = Request::has('public');

  $errors = $tag->validate();
  if ($errors) {
    Smart::assign('errors', $errors);
  } else {
    $tag->save();

    FlashMessage::add('Am salvat eticheta.', 'success');
    Util::redirect("?id={$tag->id}");
  }
}

$frequentColors = [
  'color' => Tag::getFrequentValues('color', Tag::DEFAULT_COLOR),
  'background' => Tag::getFrequentValues('background', Tag::DEFAULT_BACKGROUND),
];

$children = Model::factory('Tag')
  ->where('parentId', $tag->id)
  ->order_by_asc('value')
  ->find_many();

$used = ObjectTag::get_by_tagId($tag->id);

// tags can be deleted if (1) they have no children and (2) no objects use them
$canDelete = empty($children) && !$used;

if ($deleteButton) {
  if ($canDelete) {
    $tag->delete();
    FlashMessage::add("Am șters eticheta «{$tag->value}».", 'success');
    Util::redirectToRoute('tag/list');
  } else {
    FlashMessage::add('Nu puteți șterge eticheta deoarece (1) are descendenți sau (2) este folosită',
                      'danger');
    Util::redirect("?id={$tag->id}");
  }
}

$homonyms = Model::factory('Tag')
  ->where('value', $tag->value)
  ->where_not_equal('id', $tag->id)
  ->find_many();

$defCount = ObjectTag::count_by_objectType_tagId(
  ObjectTag::TYPE_DEFINITION, $tag->id);
$defs = $tag->loadObjects('Definition', ObjectTag::TYPE_DEFINITION, DEF_LIMIT);
$searchResults = SearchResult::mapDefinitionArray($defs);

$lexemeCount = ObjectTag::count_by_objectType_tagId(
  ObjectTag::TYPE_LEXEME, $tag->id);
$lexemes = $tag->loadObjects('Lexeme', ObjectTag::TYPE_LEXEME, LEXEME_LIMIT);

$meaningCount = ObjectTag::count_by_objectType_tagId(
  ObjectTag::TYPE_MEANING, $tag->id);
$meanings = $tag->loadObjects('Meaning', ObjectTag::TYPE_MEANING, MEANING_LIMIT);

Smart::assign([
  't' => $tag,
  'children' => $children,
  'canDelete' => $canDelete,
  'homonyms' => $homonyms,
  'defCount' => $defCount,
  'searchResults' => $searchResults,
  'lexemeCount' => $lexemeCount,
  'lexemes' => $lexemes,
  'meaningCount' => $meaningCount,
  'meanings' => $meanings,
  'frequentColors' => $frequentColors,
]);
Smart::addResources('admin', 'colorpicker', 'select2Dev');
Smart::display('tag/edit.tpl');

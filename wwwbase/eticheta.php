<?php
require_once("../phplib/util.php");
util_assertModerator(PRIV_EDIT);

$DEF_LIMIT = 20;
$LEXEM_LIMIT = 100;
$MEANING_LIMIT = 50;

$id = Request::get('id');

$tag = Tag::get_by_id($id);

// load the tag's ancestors
$hierarchy = [ $tag ];
$p = $tag;
while ($p) {
  $p = Tag::get_by_id($p->parentId);
  if ($p) {
    array_unshift($hierarchy, $p);
  }
}

$defCount = Model::factory('ObjectTag')
          ->where('objectType', ObjectTag::TYPE_DEFINITION)
          ->where('tagId', $tag->id)
          ->count();
$defs = Model::factory('Definition')
      ->table_alias('d')
      ->select('d.*')
      ->join('ObjectTag', ['ot.objectId', '=', 'd.id'], 'ot')
      ->where('ot.objectType', ObjectTag::TYPE_DEFINITION)
      ->where('ot.tagId', $tag->id)
      ->limit($DEF_LIMIT)
      ->find_many();
$searchResults = SearchResult::mapDefinitionArray($defs);

$lexemCount = Model::factory('ObjectTag')
            ->where('objectType', ObjectTag::TYPE_LEXEM)
            ->where('tagId', $tag->id)
            ->count();
$lexems = Model::factory('Lexem')
        ->table_alias('l')
        ->select('l.*')
        ->join('ObjectTag', ['ot.objectId', '=', 'l.id'], 'ot')
        ->where('ot.objectType', ObjectTag::TYPE_LEXEM)
        ->where('ot.tagId', $tag->id)
        ->limit($LEXEM_LIMIT)
        ->find_many();

$meaningCount = Model::factory('ObjectTag')
            ->where('objectType', ObjectTag::TYPE_MEANING)
            ->where('tagId', $tag->id)
            ->count();
$meanings = Model::factory('Meaning')
          ->table_alias('m')
          ->select('m.*')
          ->join('ObjectTag', ['ot.objectId', '=', 'm.id'], 'ot')
          ->where('ot.objectType', ObjectTag::TYPE_MEANING)
          ->where('ot.tagId', $tag->id)
          ->limit($MEANING_LIMIT)
          ->find_many();

SmartyWrap::assign('t', $tag);
SmartyWrap::assign('hierarchy', $hierarchy);
SmartyWrap::assign('defCount', $defCount);
SmartyWrap::assign('searchResults', $searchResults);
SmartyWrap::assign('lexemCount', $lexemCount);
SmartyWrap::assign('lexems', $lexems);
SmartyWrap::assign('meaningCount', $meaningCount);
SmartyWrap::assign('meanings', $meanings);
SmartyWrap::display('eticheta.tpl');

?>

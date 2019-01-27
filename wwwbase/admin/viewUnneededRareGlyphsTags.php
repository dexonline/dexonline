<?php
require_once '../../phplib/Core.php';
User::mustHave(User::PRIV_EDIT);
Util::assertNotMirror();

$selectedDefIds = Request::getArray('selectedDefIds', []);

$tag = Tag::get_by_id(Config::get('tags.rareGlyphsTagId'));

foreach ($selectedDefIds as $defId) {
  ObjectTag::dissociate(ObjectTag::TYPE_DEFINITION, $defId, $tag->id);
}

$defs = Definition::loadUnneededRareGlyphsTags();

SmartyWrap::assign([
  'searchResults' => SearchResult::mapDefinitionArray($defs),
  'tag' => $tag,
]);
SmartyWrap::addCss('admin');
SmartyWrap::display('admin/viewUnneededRareGlyphsTags.tpl');

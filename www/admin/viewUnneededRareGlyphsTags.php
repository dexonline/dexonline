<?php
require_once '../../lib/Core.php';
User::mustHave(User::PRIV_EDIT);

$selectedDefIds = Request::getArray('selectedDefIds', []);

$tag = Tag::get_by_id(Config::TAG_ID_RARE_GLYPHS);

foreach ($selectedDefIds as $defId) {
  ObjectTag::dissociate(ObjectTag::TYPE_DEFINITION, $defId, $tag->id);
}

$defs = Definition::loadUnneededRareGlyphsTags();

Smart::assign([
  'searchResults' => SearchResult::mapDefinitionArray($defs),
  'tag' => $tag,
]);
Smart::addCss('admin');
Smart::display('admin/viewUnneededRareGlyphsTags.tpl');

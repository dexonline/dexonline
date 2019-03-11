<?php

User::mustHave(User::PRIV_EDIT);

$sourceUrlName = Request::get('source');
$selectedDefIds = Request::getArray('selectedDefIds', []);

$tag = Tag::get_by_id(Config::TAG_ID_RARE_GLYPHS);

foreach ($selectedDefIds as $defId) {
  ObjectTag::associate(ObjectTag::TYPE_DEFINITION, $defId, $tag->id);
}

$source = Source::get_by_urlName($sourceUrlName); // possibly null
$sourceId = $source->id ?? 0;

$defs = Definition::loadMissingRareGlyphsTags($sourceId);

Smart::assign([
  'sourceId' => $sourceId,
  'searchResults' => SearchResult::mapDefinitionArray($defs),
  'tag' => $tag,
]);
Smart::addResources('admin');
Smart::display('report/missingRareGlyphsTags.tpl');

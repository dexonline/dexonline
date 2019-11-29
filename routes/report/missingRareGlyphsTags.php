<?php

User::mustHave(User::PRIV_EDIT);

$sourceId = Request::get('source');
$selectedDefIds = Request::getArray('selectedDefIds', []);

$tag = Tag::get_by_id(Config::TAG_ID_RARE_GLYPHS);

if ($selectedDefIds) {
  foreach ($selectedDefIds as $defId) {
    ObjectTag::associate(ObjectTag::TYPE_DEFINITION, $defId, $tag->id);
  }

}

$defs = Definition::loadMissingRareGlyphsTags($sourceId);

if (!count($defs) && $sourceId) {
  // probably all definitions from respective source were submitted
  // try to fetch all
  $defs = Definition::loadMissingRareGlyphsTags();
}

$sources = new SourceDropdown('getAllForRareGlyphTags', [ 'selectedValue' => $sourceId ]);


Smart::assign([
  'sources' => (array)$sources,
  'searchResults' => SearchResult::mapDefinitionArray($defs),
  'tag' => $tag,
]);
Smart::addResources('admin', 'ldring', 'bulkCheckbox');
Smart::display('report/missingRareGlyphsTags.tpl');

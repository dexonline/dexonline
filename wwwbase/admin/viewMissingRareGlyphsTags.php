<?php
require_once '../../phplib/Core.php';
User::mustHave(User::PRIV_EDIT);
Util::assertNotMirror();

$sourceUrlName = Request::get('source');
$tagButton = Request::has('tagButton');
$selectedDefIds = Request::getArray('selectedDefIds', []);

$tag = Tag::get_by_id(Config::get('tags.rareGlyphsTagId'));

if ($tagButton) {
  foreach ($selectedDefIds as $defId) {
    ObjectTag::associate(ObjectTag::TYPE_DEFINITION, $defId, $tag->id);
  }
}

$source = Source::get_by_urlName($sourceUrlName); // possibly null
$sourceId = $source->id ?? 0;

$defs = Definition::loadMissingRareGlyphsTags($sourceId);

SmartyWrap::assign([
  'sourceId' => $sourceId,
  'searchResults' => SearchResult::mapDefinitionArray($defs),
  'tag' => $tag,
]);
SmartyWrap::addCss('admin');
SmartyWrap::display('admin/viewMissingRareGlyphsTags.tpl');



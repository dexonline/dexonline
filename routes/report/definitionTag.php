<?php

User::mustHave(User::PRIV_EDIT);

$tagId = Request::get('tagId');
$tag = Tag::get_by_id($tagId);

$defs = $tag->loadObjects('Definition', ObjectTag::TYPE_DEFINITION, 1000);

Smart::assign([
  'searchResults' => SearchResult::mapDefinitionArray($defs),
  'tag' => $tag,
]);
Smart::addResources('admin');
Smart::display('report/definitionTag.tpl');

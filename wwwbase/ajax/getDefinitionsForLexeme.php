<?php
require_once("../../phplib/Core.php");

$lexemeId = Request::get('lexemeId');
$lexeme = Lexeme::get_by_id($lexemeId);
$defs = Definition::loadByEntryIds($lexem->getEntryIds());
$searchResults = SearchResult::mapDefinitionArray($defs);

SmartyWrap::assign('results', $searchResults);
SmartyWrap::displayWithoutSkin('ajax/getDefinitionsForLexem.tpl');

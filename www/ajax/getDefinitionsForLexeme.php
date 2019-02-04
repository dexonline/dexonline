<?php
require_once '../../lib/Core.php';

$lexemeId = Request::get('lexemeId');
$lexeme = Lexeme::get_by_id($lexemeId);
$defs = Definition::loadByEntryIds($lexeme->getEntryIds());
$searchResults = SearchResult::mapDefinitionArray($defs);

Smart::assign('results', $searchResults);
Smart::displayWithoutSkin('ajax/getDefinitionsForLexeme.tpl');

<?php
require_once("../../phplib/Core.php");

$lexemId = Request::get('lexemId');
$lexem = Lexem::get_by_id($lexemId);
$defs = Definition::loadByEntryIds($lexem->getEntryIds());
$searchResults = SearchResult::mapDefinitionArray($defs);

SmartyWrap::assign('results', $searchResults);
SmartyWrap::displayWithoutSkin('ajax/getDefinitionsForLexem.tpl');

?>

<?php
require_once("../../phplib/util.php");

$lexemId = Request::get('lexemId');
$lexem = Lexem::get_by_id($lexemId);
$defs = Definition::loadByEntryId($lexem->entryId);
$searchResults = SearchResult::mapDefinitionArray($defs);

SmartyWrap::assign('results', $searchResults);
SmartyWrap::displayWithoutSkin('ajax/getDefinitionsForLexem.tpl');

?>

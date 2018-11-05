<?php

require_once("../phplib/Core.php");

define('maxFreq', 1);
define('medium_easy', 0.90);
define('hard_medium', 0.80);
define('expert_hard', 0.60);
define('minFreq', 0.0);

define('easyLength', 6);
define('mediumLength', 7);
define('hardLength', 9);
define('expertLength', 30);

$difficulty = Request::get('d', 0);
switch ($difficulty) {
case 4:
  $minFreq = minFreq;
  $maxFreq = expert_hard;
  $maxLength = expertLength;
  break;
case 3:
  $minFreq = expert_hard;
  $maxFreq = hard_medium;
  $maxLength = hardLength;
  break;
case 2:
  $minFreq = hard_medium;
  $maxFreq = medium_easy;
  $maxLength = mediumLength;
  break;
default :
  $minFreq = medium_easy;
  $maxFreq = maxFreq;
  $maxLength = easyLength;
}

do {
  $lexeme = Model::factory('Lexeme')
    ->where_gte('frequency', $minFreq)
    ->where_lte('frequency', $maxFreq)
    ->where_raw('char_length(formUtf8General) >= 5')
    ->where_raw('char_length(formUtf8General) <= '. $maxLength)
    ->order_by_expr('rand()')
    ->find_one();

  // select all the definitions for the given lexeme
  $defs = Model::factory('Definition')
        ->table_alias('d')
        ->select('d.*')
        ->join('EntryDefinition', ['d.id', '=', 'ed.definitionId'], 'ed')
        ->join('EntryLexeme', ['ed.entryId', '=', 'el.entryId'], 'el')
        ->join('Source', ['s.id', '=', 'd.sourceId'], 's')
        ->where('el.lexemeId', $lexeme->id)
        ->where('d.status', Definition::ST_ACTIVE)
        ->where('s.type', Source::TYPE_OFFICIAL)
        ->order_by_asc('s.displayOrder')
        ->find_many();
// loop untill you find a lexeme with a definition
} while (!$defs);

$searchResults = SearchResult::mapDefinitionArray($defs);
$word = mb_strtoupper($lexeme->formNoAccent);

SmartyWrap::assign('wordLength', mb_strlen($word));
SmartyWrap::assign('letters', preg_split('//u', 'aăâbcdefghiîjklmnopqrsștțuvwxyz', null, PREG_SPLIT_NO_EMPTY));
SmartyWrap::assign('word', $word);
SmartyWrap::assign('searchResults', $searchResults);
SmartyWrap::assign('difficulty', $difficulty);
SmartyWrap::display("hangman.tpl");

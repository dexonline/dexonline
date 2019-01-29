<?php

require_once("../phplib/Core.php");

const MAX_FREQ = 1;
const MEDIUM_EASY = 0.90;
const HARD_MEDIUM = 0.80;
const EXPERT_HARD = 0.60;
const MIN_FREQ = 0.0;

const EASY_LENGTH = 6;
const MEDIUM_LENGTH = 7;
const HARD_LENGTH = 9;
const EXPERT_LENGTH = 30;

$difficulty = Request::get('d', 0);
switch ($difficulty) {
case 4:
  $minFreq = MIN_FREQ;
  $maxFreq = EXPERT_HARD;
  $maxLength = EXPERT_LENGTH;
  break;
case 3:
  $minFreq = EXPERT_HARD;
  $maxFreq = HARD_MEDIUM;
  $maxLength = HARD_LENGTH;
  break;
case 2:
  $minFreq = HARD_MEDIUM;
  $maxFreq = MEDIUM_EASY;
  $maxLength = MEDIUM_LENGTH;
  break;
default :
  $minFreq = MEDIUM_EASY;
  $maxFreq = MAX_FREQ;
  $maxLength = EASY_LENGTH;
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

SmartyWrap::assign([
  'wordLength' => mb_strlen($word),
  'letters' => Str::unicodeExplode('aăâbcdefghiîjklmnopqrsștțuvwxyz'),
  'word' => $word,
  'searchResults' => $searchResults,
  'difficulty' => $difficulty
]);
SmartyWrap::display("hangman.tpl");

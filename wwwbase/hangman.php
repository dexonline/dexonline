<?php

require_once("../phplib/util.php");
setlocale(LC_ALL, "ro_RO.utf8");

define('maxFreq', 1);
define('medium_easy', 0.90);
define('hard_medium', 0.80);
define('expert_hard', 0.60);
define('minFreq', 0.0);

define('easyLength', 6);
define('mediumLength', 7);
define('hardLength', 9);
define('expertLength', 30);

$difficulty = util_getRequestParameterWithDefault('d', 0);
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

$count = Model::factory('Lexem')
  ->where_gte('frequency', $minFreq)
  ->where_lte('frequency', $maxFreq)
  ->where_raw('char_length(formUtf8General) >= 5')
  ->where_raw('char_length(formUtf8General) <= '.$maxLength)
  ->count();

do {
  $lexem = Model::factory('Lexem')
    ->where_gte('frequency', $minFreq)
    ->where_lte('frequency', $maxFreq)
    ->where_raw('char_length(formUtf8General) >= 5')
    ->where_raw('char_length(formUtf8General) <= '.$maxLength)
    ->limit(1)
    ->offset(rand(0, $count - 1))
    ->find_one();

  // select all the definitions for the given lexem
  $defs = Model::factory('Definition')
    ->select('Definition.*')
    ->join('LexemDefinitionMap', 'Definition.id = definitionId')
    ->join('Source', 'Source.id = sourceId')
    ->where('lexemId', $lexem->id)
    ->where('status', 0)
    ->where('isOfficial', 2)
    ->order_by_asc('displayOrder')
    ->find_many();
// loop untill you find a lexem with a definition
} while (!$defs);

$searchResults = SearchResult::mapDefinitionArray($defs);
$word = $lexem->formNoAccent;

smarty_assign('wordLength', mb_strlen($word));
smarty_assign('letters', preg_split('//u', 'aĂâbcdefghiîjklmnopqrsȘtȚuvwxyz', null, PREG_SPLIT_NO_EMPTY));
smarty_assign('page_title', 'Spânzuratoarea');
smarty_assign('word', $word);
smarty_assign('searchResults', $searchResults);
smarty_assign('difficulty', $difficulty);
smarty_displayCommonPageWithSkin("hangman.ihtml");
?>

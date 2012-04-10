<?php

require_once("../phplib/util.php");
setlocale(LC_ALL, "ro_RO.utf8");

define('limit_freq', 1);
define('easy', 0.80);
define('normal', 0.60);
define('hard', 0.40);
define('imp', 0.20);

$difficulty = util_getRequestParameterWithDefault('d', 0);
switch ($difficulty) {
case 0:
  $minFreq = easy;
  $maxFreq = limit_freq;
  break;
case 1:
  $minFreq = normal;
  $maxFreq = easy;
  break;
case 2:
  $minFreq = hard;
  $maxFreq = normal;
  break;
default :
  $minFreq = imp;
  $maxFreq = hard;
}

$count = Model::factory('Lexem')->where_gte('frequency', $minFreq)->where_lte('frequency', $maxFreq)
  ->where_raw('length(formUtf8General) > 5')->count();

do {
  $lexem = Model::factory('Lexem')->where_gte('frequency', $minFreq)->where_lte('frequency', $maxFreq)
    ->where_raw('length(formUtf8General) > 5')->limit(1)->offset(rand(0, $count - 1))->find_one();

  // Select an official definition for this lexem.
  $defs = Model::factory('Definition')
    ->select('Definition.*')
    ->join('LexemDefinitionMap', 'Definition.id = definitionId')
    ->join('Source', 'Source.id = sourceId')
    ->where('lexemId', $lexem->id)
    ->where('status', 0)
    ->where('isOfficial', 2)
    ->order_by_asc('displayOrder')
    ->find_many();
} while (!$defs);

$searchResults = SearchResult::mapDefinitionArray($defs);
$cuv = $lexem->formNoAccent;

smarty_assign('wordLength', mb_strlen($cuv));
smarty_assign('letters', preg_split('//u', 'aăâbcdefghiîjklmnopqrsștțuvwxyz', null, PREG_SPLIT_NO_EMPTY));
smarty_assign('page_title', 'Spânzurătoarea');
smarty_assign('cuvant', $cuv);
smarty_assign('searchResults', $searchResults);
smarty_assign('difficulty', $difficulty);
smarty_displayCommonPageWithSkin("spanzuratoarea.ihtml");
?>

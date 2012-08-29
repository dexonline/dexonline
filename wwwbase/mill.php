<?php

require_once("../phplib/util.php");
setlocale(LC_ALL, "ro_RO.utf8");


function getNormalRand($std, $mean, $limit)
{
  //$std Standard Devition
  //$mean The mean
  //$limit Maximum number
  //Using Box-Muller transform
  $x1 = (float)rand(0, $limit)/(float)$limit;
  $x2 = (float)rand(0, $limit)/(float)$limit;
  
  $rand = sqrt(-2 * log($x1)) * cos(2 * pi() * $x2);
  
  return round($rand * $std + $mean);
}

$difficulty = util_getRequestParameterWithDefault('d', 0);

$count = Model::factory('DefinitionSimple')
  ->count();

$chosenDef = rand(0, $count - 1);
$answer = rand(1, 4);
  
$maindef = Model::factory('DefinitionSimple')
  ->limit(1)
  ->offset($chosenDef)
  ->find_one();

$word = Model::factory('DefinitionSimple')
    ->select('lexicon')
    ->join('Definition', 'Definition.id = definitionId')
    ->where('definitionId', $maindef->definitionId)
    ->find_one();

$options = array(
  1 => null,
  2 => null,
  3 => null,
  4 => null
);

$options[$answer] = $maindef->getDisplayValue();

for($i=1;$i<=4;$i++)
{
  if($i!=$answer)
  {
    $def = Model::factory('DefinitionSimple')
      ->limit(1)
      ->offset(getNormalRand(30, $chosenDef ,$count - 1))
      ->find_one();
    $options[$i] = $def->getDisplayValue();
  }
}

smarty_assign('answer', $answer);
smarty_assign('page_title', 'Moara cuvintelor');
smarty_assign('word', $word->lexicon);
smarty_assign('options', $options);
smarty_assign('difficulty', $difficulty);
smarty_displayCommonPageWithSkin("mill.ihtml");
?>

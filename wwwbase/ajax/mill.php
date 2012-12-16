<?php

require_once("../../phplib/util.php");
setlocale(LC_ALL, "ro_RO.utf8");
$xml = new SimpleXMLElement('<xml/>');


function getNormalRand($std, $mean, $limit) {
  //$std Standard Deviation
  //$mean The mean
  //$limit Maximum number
  //Using Box-Muller transform
  $x1 = (float)rand(0, $limit)/(float)$limit;
  $x2 = (float)rand(0, $limit)/(float)$limit;
  
  $rand = sqrt(-2 * log($x1)) * cos(2 * pi() * $x2);
  
  // Make sure the result is between 0 and $limit inclusive
  return min(max(round($rand * $std + $mean), 0), $limit);
}

$difficulty = util_getRequestParameterWithDefault('d', 0);
$logAnswerId = util_getRequestParameterWithDefault('answerId', 0);
$logGuessed = util_getRequestParameterWithDefault('guessed', 0);

if($logAnswerId!=0) {
  $log = Model::factory('DefinitionSimple')
      ->select('*')
      ->where('id',$logAnswerId)
      ->find_one();
 
  $log->millShown++;
  $log->millGuessed = $log->millGuessed + $logGuessed;
  $log->save();
}

$count = Model::factory('DefinitionSimple')->count();

$chosenDef = rand(0, $count - 1);
$answer = rand(1, 4);
  
$maindef = Model::factory('DefinitionSimple')->limit(1)->offset($chosenDef)->find_one();

$word = Model::factory('DefinitionSimple')
  ->select('d.lexicon')
  ->join('Definition', 'd.id = definitionId', 'd')
  ->where('definitionId', $maindef->definitionId)
  ->find_one();

$options = array();
$options[$answer] = $maindef->getDisplayValue();
$used[$maindef->definitionId] = 1;

for ($i = 1; $i <= 4; $i++) {
  if ($i != $answer) {
    do {
      if ($difficulty == 1) {
        $aux = rand(0, $count - 1);
      } else {
        $aux = getNormalRand(100 - ($difficulty * 20), $chosenDef, $count - 1);
      }
      $def = Model::factory('DefinitionSimple')->limit(1)->offset($aux)->find_one();
    } while (array_key_exists($def->definitionId, $used));
    $used[$def->definitionId] = 1;
    $options[$i] = $def->getDisplayValue();
  }
}

$xml->addChild('word', $word->lexicon);
$xml->addChild('answerId', $maindef->id);
for ($i = 1; $i <= 4; $i++) {
  $xml->addChild('definition' . $i, $options[$i]);
}
$xml->addChild('answer', $answer);

print($xml->asXML());
?>

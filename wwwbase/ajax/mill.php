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

function getWordForDefinitionId($defId) {
  $def = Definition::get_by_id($defId);
  return $def->lexicon;
}

function getSimpleDefinitionsForLexemIds($lexemIds) {
  $defIds = Model::factory('LexemDefinitionMap')
          ->select('definitionId')
          ->distinct()
          ->where_in('lexemId', $lexemIds)
          ->find_many();
  $defIds = array_map(function ($def){return $def->definitionId;}, $defIds);
    
  $defs = Model::factory('DefinitionSimple')
        ->where_in('definitionId', $defIds)
        ->find_many();
    
  return $defs;
}

$difficulty = util_getRequestParameterWithDefault('d', 0);
$logAnswerId = util_getRequestParameterWithDefault('answerId', 0);
$logGuessed = util_getRequestParameterWithDefault('guessed', 0);

// Log the success of failure of the previous guess, if any
// TODO: the last (tenth) guess is never logged
if ($logAnswerId!=0) {
  $log = DefinitionSimple::get_by_id($logAnswerId);
  $log->millShown++;
  $log->millGuessed += $logGuessed;
  $log->save();
}

$count = Model::factory('DefinitionSimple')->count();

$chosenDef = rand(0, $count - 1);
$answer = rand(1, 4);
  
$maindef = Model::factory('DefinitionSimple')->limit(1)->offset($chosenDef)->find_one();

$word = getWordForDefinitionId($maindef->definitionId);

$options = [];
$options[$answer] = [
  'term' => $word,
  'text' => $maindef->getDisplayValue(),
];
$used[$maindef->definitionId] = 1;

$closestLexemsDefinitionsCount = null;
$closestLexemsDefinitions = null;
if ($difficulty > 1) {
  $nearLexemIds = NGram::searchLexemIds($word);
  arsort($nearLexemIds);
  $lexemPoolSize = 48/$difficulty;
  $closestLexemIds = array_slice($nearLexemIds, 0, $lexemPoolSize, true);
  $closestLexemIds = array_keys($closestLexemIds);
  
  $closestLexemsDefinitions = getSimpleDefinitionsForLexemIds($closestLexemIds);
  $closestLexemsDefinitionsCount = count($closestLexemsDefinitions);
  
  //if there are no close lexem definitions to choose from 
  //then use easier difficulty
  if ($closestLexemsDefinitionsCount == 0) {
    $difficulty = 1;
  }
}

for ($i = 1; $i <= 4; $i++) {
  $def = null;  
  if ($i != $answer) {
    do {
      if ($difficulty == 1) {
        $aux = rand(0, $count - 1);
        $def = Model::factory('DefinitionSimple')->limit(1)->offset($aux)->find_one();
      } else {
        $aux = rand(0, $closestLexemsDefinitionsCount - 1);
        $def = $closestLexemsDefinitions[$aux];
        
        unset($closestLexemsDefinitions[$aux]);
        $closestLexemsDefinitions = array_values($closestLexemsDefinitions);
        $closestLexemsDefinitionsCount--;
        
        //if we run out of close lexem definitions to use 
        //then use easier difficulty
        if ($closestLexemsDefinitionsCount == 0) {
          $difficulty = 1;
        }
      }
    } while (array_key_exists($def->definitionId, $used));
  
    
    $used[$def->definitionId] = 1;
    $options[$i] = [
      'term' => getWordForDefinitionId($def->definitionId),
      'text' => $def->getDisplayValue(),
    ];
  }
}


$xml->addChild('word', $word);
$xml->addChild('answerId', $maindef->id);
for ($i = 1; $i <= 4; $i++) {
  $def = $xml->addChild('definition' . $i);
  $def->addChild('term', $options[$i]['term']);
  $def->addChild('text', $options[$i]['text']);
}
$xml->addChild('answer', $answer);

print($xml->asXML());
?>

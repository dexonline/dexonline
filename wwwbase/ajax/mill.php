<?php

require_once("../../phplib/util.php");

function getWordForDefinitionId($defId) {
  $def = Definition::get_by_id($defId);
  return $def->lexicon;
}

function getSimpleDefinitionsForLexemIds($lexemIds) {
  $defIds = Model::factory('EntryDefinition')
          ->table_alias('ed')
          ->select('definitionId')
          ->distinct()
          ->join('EntryLexem', ['ed.entryId', '=', 'el.entryId'], 'el')
          ->join('Lexem', ['el.lexemId', '=', 'l.id'], 'l')
          ->where_in('l.id', $lexemIds)
          ->find_many();
  $defIds = util_objectProperty($defIds, 'definitionId');
    
  $defs = Model::factory('DefinitionSimple')
        ->where_in('definitionId', $defIds)
        ->find_many();
    
  return $defs;
}

$difficulty = Request::get('d');
$logDefId = Request::get('defId');
$logGuessed = Request::get('guessed');

// Log the success or failure of the previous guess, if any
// TODO: the last (tenth) guess is never logged
if ($logDefId) {
  $log = DefinitionSimple::get_by_id($logDefId);
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
  $lexemPoolSize = 48 / $difficulty;
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

$resp = [
  'word' => $word,
  'defId' => $maindef->id,
  'answer' => $answer,
  'definition' => $options,
];

header('Content-Type: application/json');
print json_encode($resp);

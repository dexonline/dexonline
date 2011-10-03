<?php

// WARNING! Run AFTER applying schemaChanges.sql
//
// Algorithm: All the words having Priority = 0 and identical Names
// are merged into one concept. Let {D_i} be the set of definition IDs
// for these words. Then all the words having Priority > 0 and a
// definition ID from {D_i} are added to the list of words for that
// concept.

require_once("../../phplib/util.php");
ini_set("memory_limit", "512000000");
ini_set('max_execution_time', '3600');

$definitionIdToConceptIdMap = array();
$conceptIdToWordsMap = array();

// For a series of words with Priority = 0 and equal Names, this variable
// stores the first word.
$exponent = Word::create('###', -1, -1);

// Go through all the words having Priority = 0, which define the concepts.
$dbResult = mysql_query("select * from Word where Priority = 0 " .
                        "order by Name");
$numRows = mysql_num_rows($dbResult);
$i = 0;
while ($dbRow = mysql_fetch_assoc($dbResult)) {
  $i++;
  $word = new Word();
  $word->populateFromDbRow($dbRow);
  $definitionId = $word->conceptId; // We changed the column name already
  print "$i/$numRows " . $word->name . "... ";

  if ($exponent->name == $word->name) {
    // We already have a concept created for this name
    $conceptId = $exponent->conceptId;
    migrateDeclensionModels($word->id, $exponent->id);
    $word->delete();
    print "deleted.\n";
  } else {
    $concept = Concept::create($word->name, '');
    $concept->save();
    $conceptId = db_getLastInsertedId();
    $conceptIdToWordsMap[$conceptId] = array($word->name => $word->id);
    $word->conceptId = $conceptId;
    $word->save();
    $exponent = $word;
    print "created concept.\n";
  }
  $definitionIdToConceptIdMap[$definitionId] = $conceptId;
  $cdm = ConceptDefinitionMap::create($conceptId, $definitionId);
  $cdm->save();
}
mysql_free_result($dbResult);

// Go through all the words having Priority > 0
$dbResult = mysql_query("select * from Word where Priority > 0");
$numRows = mysql_num_rows($dbResult);
$i = 0;
while ($dbRow = mysql_fetch_assoc($dbResult)) {
  $i++;
  $word = new Word();
  $word->populateFromDbRow($dbRow);
  $definitionId = $word->conceptId; // We changed the column name already
  $conceptId = $definitionIdToConceptIdMap[$definitionId];
  $wordsMap = $conceptIdToWordsMap[$conceptId];
  print "$i/$numRows " . $word->name . "... ";

  // Check if this term was already added
  if (array_key_exists($word->name, $wordsMap)) {
    $obsoletedByWordId = $wordsMap[$word->name];
    migrateDeclensionModels($word->id, $obsoletedByWordId);
    $word->delete();
    print "deleted.\n";
  } else {
    $word->priority = count($wordsMap);
    $word->conceptId = $conceptId;
    $word->save();
    $conceptIdToWordsMap[$conceptId][$word->name] = $word->id;
    print "added to concept.\n";
  }
}
mysql_free_result($dbResult);

// Go through all the deleted definitions and unassociate them
$dbResult = mysql_query('select * from Definition where Status = 2');
$numRows = mysql_num_rows($dbResult);
$i = 0;
while ($dbRow = mysql_fetch_assoc($dbResult)) {
  $i++;
  print "$i/$numRows definitions unassociated...\n";
  $def = new Definition();
  $def->populateFromDbRow($dbRow);
  $cdms = ConceptDefinitionMap::loadByDefinitionId($def->id);
  foreach ($cdms as $cdm) {
    $cdm->delete();
  }
}
mysql_free_result($dbResult);

// Go through all the unassociated concepts (that have become unassociated
// during the above step) and delete them and their Words.
$concepts = Concept::loadUnassociated();
$i = 0;
foreach ($concepts as $concept) {
  $i++;
  print "Deleting concept $i/" . count($concepts) . "\n";
  $concept->delete();
}

return;




// When merging n words with Priority = 0 and equals Names into one
// word, we'll end up deleting n-1 words. If those words have DeclensionModels,
// transfer those to the one word that we keep. However, make sure not to
// create several DeclensionModels with the same WordId and PartOfSpeechId.
function migrateDeclensionModels($oldWordId, $newWordId) {
  $oldDmList = DeclensionModel::loadByWordId($oldWordId);
  $newDmList = DeclensionModel::loadByWordId($newWordId);
  
  $seenPartOfSpeechIds = array();
  foreach ($newDmList as $dm) {
    $seenPartOfSpeechIds[$dm->partOfSpeechId] = true;
  }

  foreach ($oldDmList as $dm) {
    if (!array_key_exists($dm->partOfSpeechId, $seenPartOfSpeechIds)) {
      print "migrating one DM... ";
      $seenPartOfSpeechIds[$dm->partOfSpeechId] = true;
      $dm->wordId = $newWordId;
      $dm->save();
    }
  }
}

?>

<?php
require_once("../../phplib/util.php");
ini_set('max_execution_time', '3600');
ini_set("memory_limit", "512000000");
assert_options(ASSERT_BAIL, 1);
debug_off();

createLexemDefinitionMap();
associateLongInfinitivesAndParticiples();

function createLexemDefinitionMap() {
  LexemDefinitionMap::deleteAll();
  $dbResult = db_selectAllConcepts();
  
  print "Migrating " . mysql_num_rows($dbResult) . " concepts...\n";
  
  $seen = 0;
  while ($dbRow = mysql_fetch_assoc($dbResult)) {
    $concept = new Concept();
    $concept->populateFromDbRow($dbRow);
    $words = Word::loadByConceptId($concept->id);
    $definitions = Definition::loadByConceptId($concept->id);
    
    if ($definitions) {
      // Select distinct words
      $distinctWords = array();
      foreach ($words as $word) {
        $distinctWords[$word->name] = 1;
      }
      
      // For every word, look up all the lexems. Then map each of those lexems
      // to every definition.
      foreach ($distinctWords as $word => $ignored) {
        $lexems = Lexem::loadByUnaccented($word);
        
        // Create lexem if necessary so that we don't lose any words during the
        // migration
        if (count($lexems) == 0) {
          $lexem = Lexem::create($word, 'T', 1, '');
          $lexem->save();
          $lexem->id = db_getLastInsertedId();
          $lexems[] = $lexem;
          
          $lexem->regenerateParadigm();
        }
        
        foreach ($lexems as $lexem) {
          foreach ($definitions as $definition) {
            $ldm = LexemDefinitionMap::load($lexem->id, $definition->id);
            if (!$ldm) {
              $ldm = LexemDefinitionMap::create($lexem->id, $definition->id);
              $ldm->save();
            }
          }
        }
      }
    }
    
    $seen++;
    if ($seen % 1000 == 0) {
      print "Seen: $seen;\n";
    }
  }
  print "Seen: $seen;\n";
}

// Here we look at all the unassociated lexems and see if they
// correspond to a long infinitive or participle. If so, associate
// them with the same definitions as the verb itself
function associateLongInfinitivesAndParticiples() {
  $lexems = Lexem::loadUnassociated();
  $numMatched = 0;

  foreach ($lexems as $l) {
    $matched = false;
    $wordlist = WordList::loadByUnaccented($l->unaccented);
    
    foreach ($wordlist as $wl) {
      if ($wl->inflectionId == 50 || $wl->inflectionId == 52) {
        $verb = Lexem::load($wl->lexemId);
        print "{$l->unaccented} :: {$verb->unaccented}\n";
        $matched = true;

        $ldms = LexemDefinitionMap::loadByLexemId($verb->id);
        foreach ($ldms as $ldm) {
          $existingLdm = LexemDefinitionMap::load($l->id, $ldm->definitionId);
          if (!$existingLdm) {
            $newLdm = LexemDefinitionMap::create($l->id, $ldm->definitionId);
            $newLdm->save();
          }
        }
      }
    }
    if ($matched) {
      $numMatched++;
    }
  }
  print "Matched $numMatched of " . count($lexems) . " total lexems.\n";
}

?>

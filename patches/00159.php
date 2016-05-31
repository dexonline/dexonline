<?php

// Move LexemModel-related data to Lexem.
// Split lexems with multiple models into one lexem per model.

define('BATCH_SIZE', 1000);

$offset = 0;

do {
  $lexems = Model::factory('Lexem')
          ->limit(BATCH_SIZE)
          ->offset($offset)
          ->find_many();

  foreach ($lexems as $orig) {
    if (!$orig->modelType) { // otherwise it's already been migrated
      $lms = $orig->getLexemModels();

      foreach ($lms as $i => $lm) {
        if ($i) {
          // create a new lexem
          $l = $orig->parisClone();
        } else {
          $l = $orig;
        }

        // copy LexemModel data
        $l->modelType = $lm->modelType;
        $l->modelNumber = $lm->modelNumber;
        $l->restriction = $lm->restriction;
        $l->notes = $lm->tags;
        $l->isLoc = $lm->isLoc;
        $l->save();

        // update LexemSource foreign keys
        $set = LexemSource::get_all_by_lexemModelId($lm->id);
        foreach ($set as $obj) {
          $obj->lexemId = $l->id;
          $obj->save();
        }

        // update InflectedForm foreign keys
        $set = InflectedForm::get_all_by_lexemModelId($lm->id);
        foreach ($set as $obj) {
          $obj->lexemId = $l->id;
          $obj->save();
        }
      }
    }
  }

  $offset += BATCH_SIZE;
  Log::info("Processed $offset lexems.");
} while (count($lexems));

<?php

// Create one entry for each lexem

define('BATCH_SIZE', 1000);

$offset = 0;

do {
  $lexems = Model::factory('Lexem')
          ->limit(BATCH_SIZE)
          ->offset($offset)
          ->find_many();

  foreach ($lexems as $l) {
    if (!$l->entryId) {
      $e = Model::factory('Entry')->create();
      $e->description = (string)$l;
      $e->save();

      $l->entryId = $e->id;
      $l->save();
    }
  }

  $offset += BATCH_SIZE;
  Log::info("Processed $offset lexems.");
} while (count($lexems));

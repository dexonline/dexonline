<?php

// Merges entries like 'ntrare', having only apheresis lexemes, into the non-apheresis form

require_once __DIR__ . '/../lib/Core.php';

$aphLexemes = Model::factory('Lexeme')
  ->where('apheresis', true)
  ->order_by_asc('formNoAccent')
  ->find_many();

foreach ($aphLexemes as $al) {
  $entries = $al->getEntries();
  foreach ($entries as $e) {
    if (count($e->getLexemes()) == 1) {
      // merging, but into what?
      $full = 'î' . $al->formNoAccent;
      $destinations = Model::factory('Entry')
        ->table_alias('e')
        ->select('e.*')
        ->distinct()
        ->join('EntryLexeme', ['e.id', '=', 'el.entryId'], 'el')
        ->join('Lexeme', ['el.lexemeId', '=', 'l.id'], 'l')
        ->where('l.formNoAccent', $full)
        ->find_many();

      if (count($destinations) == 1) {
        $dest = $destinations[0];
        Log::info('Merging entry %s into %s', $e, $dest);

        $e->mergeInto($dest->id);

      } else {
        Log::info('Cannot merge %s because there are %d destinations for %s',
                  $e->description, count($destinations), $full);
      }
    }
  }
}

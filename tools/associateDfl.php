<?php

/**
 * Create proper lexemes and entries for genera and species.
 **/

require_once __DIR__ . '/../phplib/Core.php';

define('SOURCE_ID', 63);

$defs = Model::factory('Definition')
      ->where('sourceId', SOURCE_ID)
      ->where('status', Definition::ST_ACTIVE)
      ->order_by_asc('lexicon')
      ->find_many();

foreach ($defs as $d) {
  
  if (preg_match('/^@([A-ZÉË]+)@ /', $d->internalRep, $matches)) {
    // Genus
    $genus = ucfirst(mb_strtolower($matches[1]));
    fixGenus($d, $genus);
  } else if (preg_match('/^@([A-Z][a-zéë]+) (x |var\. )?([-a-zë]+)@/', $d->internalRep, $matches)) {
    // Genus species
    $genus = $matches[1];
    $species = $matches[3];
    // printf("%s gen %s specie %s\n", $matches[0], $genus, $species);
  } else {
    $genus = null;
    $species = null;
    printf("Nu pot deduce numele speciei: [%s] %s%d\n",
           mb_substr($d->internalRep, 0, 80),
           "https://dexonline.ro/admin/definitionEdit.php?definitionId=",
           $d->id);
  }

}

/*************************************************************************/

function fixGenus($d, $genus) {
  print "**** Verific intrările pentru genul {$genus}\n";
  $genusEntries = Model::factory('Entry')
                ->tableAlias('e')
                ->select('e.*')
                ->join('EntryDefinition', ['e.id', '=', 'ed.entryId'], 'ed')
                ->where('ed.definitionId', $d->id)
                ->where_equal('e.description', $genus)
                ->find_many();
  $otherEntries = Model::factory('Entry')
                ->tableAlias('e')
                ->select('e.*')
                ->join('EntryDefinition', ['e.id', '=', 'ed.entryId'], 'ed')
                ->where('ed.definitionId', $d->id)
                ->where_not_equal('e.description', $genus)
                ->order_by_asc('e.description')
                ->find_many();
  if (empty($genusEntries)) {
    printf("Genul nu are intrare: [%s] %s%d\n",
           mb_substr($d->internalRep, 0, 80),
           "https://dexonline.ro/admin/definitionEdit.php?definitionId=",
           $d->id);
  }
}

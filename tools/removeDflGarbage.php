<?php

/**
 * Remove entries associated only with species lexemes (model I2.2).
 * Species lexemes by themselves should not have entries (only genus+species pairs).
 **/

require_once __DIR__ . '/../phplib/Core.php';

$query = <<<EOD
  select * from Entry e where id not in (
    select e.id
    from Entry e
    join EntryLexem el on e.id = el.entryId
    join Lexem l on el.lexemId = l.id
    where l.modelType != 'I'
    or l.modelNumber != '2.2'
  )
  order by description
EOD;

$entries = Model::factory('Entry')->raw_query($query)->find_many();

foreach ($entries as $e) {
  printf("**** {$e}\n");
  foreach ($e->getLexems() as $l) {
    printf("  * lexem: {$l} {$l->modelType}{$l->modelNumber}\n");
  }

  foreach ($e->getTrees() as $t) {
    if (($t->description == $e->description) && !$t->hasMeanings()) {
      $t->delete();
    }
  }

  $e->delete();
}

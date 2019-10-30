<?php

// Find lexemes that have duplicate paradigms, i.e. duplicate forms for some
// inflectionId. Save those lexemes.

require_once __DIR__ . '/../lib/Core.php';
ini_set('max_execution_time', '3600');

$query = <<<SQL
select id, form, modDate
  from Lexeme
  where id in (
    select i1.lexemeId
      from InflectedForm i1, InflectedForm i2
      where i1.form = i2.form
        and i1.lexemeId = i2.lexemeId
        and i1.inflectionId = i2.inflectionId
        and i1.id != i2.id
  )
  order by modDate
SQL;

print "Running query, this could take a minute...\n";

$results = DB::execute($query);

foreach ($results as $r) {
  $lexeme = Lexeme::get_by_id($r['id']);
  print "Saving lexeme {$lexeme->id} ({$lexeme->form})\n";
  $lexeme->deepSave();
}

<?php
/**
 * Some values in reverse field of table Lexeme
 * are saved incorrectly, with a space as first char
 */
require_once __DIR__ . '/../lib/Core.php';

$lexemes = Model::factory('Lexeme')
    ->where_like('reverse', ' %')
    ->find_many();

foreach ($lexemes as $l) {
  $oldReverse = $l->reverse;
  Log::info('Converting in Lexeme at id [%d], field `reverse` from [%s] to [%s]',
    $l->id, $oldReverse, $l->reverse);
  $l->save();
}

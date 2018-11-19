<?php

exit;

ini_set('memory_limit', '1G');

// generate apheresis forms for lexemes supporting apheresis
$lexemes = Model::factory('Lexeme')
  ->where('apheresis', true)
  ->order_by_asc('formNoAccent')
  ->find_many();

foreach ($lexemes as $i => $l) {
  Log::info('Regenerating %d of %d: %s', $i + 1, count($lexemes), $l->formNoAccent);
  $l->regenerateParadigm();
}

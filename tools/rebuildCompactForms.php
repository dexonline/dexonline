<?php

require_once __DIR__ . '/../lib/Core.php';

/**
 * @param array $forms An array obtained from Idiorm's find_many()
 */
function run(array $forms, string $fileName) {
  $forms = array_column($forms, 'form');
  $s = Str::compactForms($forms);
  StaticUtil::putContents($s, $fileName);
}

function main() {
  Log::notice('started');

  ini_set('memory_limit', '512M');

  // everything -- for Levenshtein
  Log::info('building compact forms for everything');
  $forms = Model::factory('Lexeme')
    ->select_expr('lower(formNoAccent)', 'form')
    ->distinct()
    ->order_by_asc('formNoAccent')
    ->find_array();
  run($forms, 'download/compact-forms/all.txt');

  // letter by letter -- for autocomplete
  foreach (range('a', 'z') as $first) {
    Log::info('building compact forms starting with %s', ucfirst($first));
    $forms = Model::factory('Lexeme')
      ->select('formNoAccent', 'form')
      ->distinct()
      ->where_like('formUtf8General', "{$first}%") // include ș* for s* etc.
      ->order_by_asc('formNoAccent')
      ->find_array();
    run($forms, "download/compact-forms/{$first}.txt");
  }

  Log::notice('finished, %d MB used', memory_get_peak_usage() >> 20);
}

main();

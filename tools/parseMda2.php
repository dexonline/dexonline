<?php

/**
 * Structure definitions from DEX '98.
 **/

require_once __DIR__ . '/../phplib/Core.php';
require_once __DIR__ . '/../phplib/third-party/PHP-parsing-tool/Parser.php';

ini_set('memory_limit', '512M');

define('SOURCE_ID', 53);
define('BATCH_SIZE', 10000);
define('START_AT', '');
define('DEBUG', false);

$offset = 0;

do {
  $defs = Model::factory('Definition')
    ->where('sourceId', SOURCE_ID)
    ->where('status', Definition::ST_ACTIVE)
    ->where_gte('lexicon', START_AT)
    ->order_by_asc('lexicon')
    ->order_by_asc('id')
    ->limit(BATCH_SIZE)
    ->offset($offset)
    ->find_many();

  foreach ($defs as $d) {
    $orig = $d->internalRep;
    $warnings = [];
    $d->parse($warnings);
    if ($orig != $d->internalRep) {
      printf("%s\n", defUrl($d));
      wdiff($orig, $d->internalRep);

      if (readCommand('Acceptați [d/n]?', ['d', 'n']) == 'd') {
        $d->save();
      }
    }
  }

  $offset += count($defs);
  Log::info("Processed $offset definitions.");
} while (count($defs) == BATCH_SIZE);

Log::info('ended');

/*************************************************************************/

function defUrl($d) {
  return "https://dexonline.ro/admin/definitionEdit.php?definitionId={$d->id}";
}

function wdiff($old, $new) {
  file_put_contents('/tmp/old.txt', $old . "\n");
  file_put_contents('/tmp/new.txt', $new . "\n");
  system(
    "wdiff -w $'\033[30;41m' -x $'\033[0m' " .
    "-y $'\033[30;42m' -z $'\033[0m' " .
    "/tmp/old.txt /tmp/new.txt");
}

function readCommand($msg, $choices) {
  do {
    $answer = mb_strtolower(readline($msg. ' '));
  } while (!in_array($answer, $choices));
  return $answer;
}

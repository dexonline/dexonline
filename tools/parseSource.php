<?php

/**
 * Structure definitions from a given source.
 **/

require_once __DIR__ . '/../lib/Core.php';
require_once __DIR__ . '/../lib/third-party/PHP-parsing-tool/Parser.php';

const BATCH_SIZE = 10000;

function error(string $msg) {
  print "$msg\n";
  exit(1);
}

function usage() {
  error(
    "Usage: parseSource.php\n" .
    "  [--debug]\n" .
    "  [--minor <n>]          automatically fix differences of size at most <n>\n" .
    "  --source <urlName>     source to parse, e.g. doom3 or mda2\n" .
    "  [--start <word>]       process definitions starting at <word>\n"
  );
}

function defUrl($d) {
  return "http://localhost/dexonline/www/editare-definitie?definitionId={$d->id}";
}

function wdiff($old, $new) {
  file_put_contents('/tmp/old.txt', $old . "\n");
  file_put_contents('/tmp/new.txt', $new . "\n");
  system(
    "wdiff -w '\033[30;41m' -x '\033[0m' " .
    "-y '\033[30;42m' -z '\033[0m' " .
    "/tmp/old.txt /tmp/new.txt");
}

function readCommand($msg, $choices) {
  do {
    $answer = mb_strtolower(readline($msg. ' '));
  } while (!in_array($answer, $choices));
  return $answer;
}

function main() {
  ini_set('memory_limit', '512M');

  $opts = getopt('', [
    'debug', 'minor:', 'source:', 'start:',
  ]);
  $DEBUG = isset($opts['debug']);
  $MINOR = (int)($opts['minor'] ?? 0);
  $SOURCE_URL_NAME = $opts['source'] ?? '';
  $SOURCE = Source::get_by_urlName($SOURCE_URL_NAME);
  $START = $opts['start'] ?? '';

  if (!$SOURCE) {
    usage();
  }

  $offset = 0;

  do {
    $defs = Model::factory('Definition')
      ->where('sourceId', $SOURCE->id)
      ->where('status', Definition::ST_ACTIVE)
      ->where_gte('lexicon', $START)
      ->order_by_asc('lexicon')
      ->order_by_asc('id')
      ->limit(BATCH_SIZE)
      ->offset($offset)
      ->find_many();

    foreach ($defs as $i => $d) {
      if ($DEBUG) {
        printf("Considering definition %d/%d [%s] [%s]\n",
                $i + 1, count($defs), $d->lexicon, $d->internalRep);
      }
      $orig = $d->internalRep;
      $errors = [];
      $warnings = [];
      $d->parse($warnings, $errors);
      if ($warnings || $errors) {
        printf("%s\n", defUrl($d));
        foreach ($warnings as $w) {
          if (!is_array($w)) {
            print "  * {$w}\n";
          }
        }
        foreach ($errors as $e) {
          if (!is_array($e)) {
            print "  * {$e}\n";
          }
        }
      }
      if (($orig != $d->internalRep) && count($errors)) {
        wdiff($orig, $d->internalRep);

        $minor = (abs(strlen($orig) - strlen($d->internalRep)) <= $MINOR);

        if ($minor ||
            readCommand('Acceptați [d/n]?', ['d', 'n']) == 'd') {
          $d->save();
        }
      }
    }

    $offset += count($defs);
    Log::info("Processed $offset definitions.");
  } while (count($defs) == BATCH_SIZE);

  Log::info('ended');
}

main();

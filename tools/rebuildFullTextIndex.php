<?php

require_once __DIR__ . '/../lib/Core.php';

/**
 * Truncates and rebuilds the FullTextIndex. Computes the entire index in
 * memory so that we can output it in primary key order. This makes the
 * inserts much faster.
 */

/**
 * Compares two numerically indexed arrays of equal lenghts.
 */
function cmp(array $a, array $b) {
  $i = 0;
  while (($i < count($a)) && ($a[$i] == $b[$i])) {
    $i++;
  }

  return ($i == count($a))
    ? 0
    : $a[$i] <=> $b[$i];
}

function getMemory($peak = false) {
  $m = $peak ? memory_get_peak_usage() : memory_get_usage();
  return round($m / 1048576);
}

/**
 * Bulk insert with hints from
 * https://dev.mysql.com/doc/refman/8.0/en/optimizing-innodb-bulk-data-loading.html
 */
class BulkInsert {
  private string $query = '';
  private int $queryLen = 0; // avoid numerous strlen($this->query)
  private int $queryLimit;
  private int $records = 0;

  function __construct() {
    // Query length is limited only by max_packet_size. That could afford us
    // millions of rows, but we'll put a reasonable cap on that.
    $result = DB::getArrayOfRows('show variables like "max_allowed_packet"');
    $maxPacketSize = (int)$result[0]['Value'];
    $this->queryLimit = min($maxPacketSize * 0.9, 1000000);

    DB::execute('SET unique_checks=0');
    DB::execute('SET autocommit=0');
  }

  function push(array $row) {
    $str = '(' . implode(',', $row) . ')';
    if ($this->query) {
      $str = ',' . $str;
    }
    $this->query .= $str;
    $this->queryLen += strlen($str);
    $this->records++;

    if ($this->queryLen > $this->queryLimit) {
      $this->run();
    }
  }

  function run() {
    if (!$this->query) {
      return;
    }

    DB::execute('start transaction');
    DB::execute(
      'insert into FullTextIndex values ' . $this->query);
    DB::execute('commit');
    // Log::debug(sprintf('Inserted %d rows. | %d MB', $this->records, getMemory()));
    $this->query = '';
    $this->queryLen = 0;
  }

  function end() {
    $this->run(); // call insert with remaining tuples

    DB::execute('SET autocommit=1');
    DB::execute('SET unique_checks=1');
  }
}

/**
 * Builds a map of inflectedForm => concatenated lexemeIds.
 */
function buildInflectedFormMap() {
  Log::info('Building inflected form map.');

  $dbResult = DB::execute(
    'select formNoAccent, group_concat(lexemeId) as lexemeIds ' .
    'from InflectedForm ' .
    'group by formNoAccent');
  $ifMap = [];

  foreach ($dbResult as $r) {
    $form = mb_strtolower($r['formNoAccent']);
    $s = isset($ifMap[$form])
      ? ($ifMap[$form] . ',')
      : '';
    $s .= $r['lexemeIds'];
    $ifMap[$form] = $s;
  }

  // The lexemeIds for each form are not necessarily unique due to (1)
  // identical forms for different inflections and (2) upper/lowercase
  // (Țuț/țuț). Make them unique.
  foreach ($ifMap as $lexemeId => $str) {
    $parts = explode(',', $str);
    $parts = array_unique($parts);
    $ifMap[$lexemeId] = implode(',', $parts);
  }

  Log::info('Inflected form map has %d entries | %d MB', count($ifMap), getMemory());
  return $ifMap;
}

function extractWords($s) {
  // throw away hidden text
  $s = preg_replace('/▶.*◀/sU', '', $s);

  // throw away footnotes
  $s = preg_replace('/(?<!\\\\)\{\{.*\}\}/sU', '', $s);

  $s = mb_strtolower($s);
  $s = Str::removeAccents($s);

  $words = preg_split('/\P{L}+/u', $s, null, PREG_SPLIT_NO_EMPTY);
  return $words;
}

/**
 * Reads all definitions and builds a map of
 * $lexemeId => concatenated ($definitionId, $position) tuples.
 */
function buildInMemoryFti(array &$ifMap, array &$stopWordForms) {
  $dbResult = DB::execute('select id, internalRep from Definition where status = 0');
  $count = 0;
  $map = [];

  foreach ($dbResult as $dbRow) {
    $words = extractWords($dbRow[1]);

    foreach ($words as $position => $word) {
      if (!isset($stopWordForms[$word])) {
        if (array_key_exists($word, $ifMap)) {
          $lexemeIds = explode(',', $ifMap[$word]);
          foreach ($lexemeIds as $lexemeId) {
            $chunk = $dbRow[0] . ',' . $position;
            if (isset($map[$lexemeId])) {
              $map[$lexemeId] .= '|' . $chunk;
            } else {
              $map[$lexemeId] = $chunk;
            }
          }
        }
      }
    }

    if (++$count % 10000 == 0) {
      $runTime = DebugInfo::getRunningTimeInMillis() / 1000;
      $speed = round($count / $runTime);
      Log::info('%d definitions scanned (%d defs/sec) | %d MB',
                $count, $speed, getMemory());
    }
  }
  Log::info("%d definitions scanned.", $count);

  return $map;
}

function traverseInMemoryFti(array &$fti) {

  $insert = new BulkInsert();

  ksort($fti); // ensure lexemeId order
  $count = 0;

  foreach ($fti as $lexemeId => $str) {
    $tuples = explode('|', $str);

    $data = [];
    foreach ($tuples as $tuple) {
      $data[] = explode(',', $tuple);
    }
    usort($data, 'cmp'); // sort in primary key order
    foreach ($data as $pair) {
      $insert->push([ $lexemeId, $pair[0], $pair[1] ]);

      if (++$count % 1000000 == 0) {
        Log::info('%d rows merged from the in-memory FTI. | %d MB',
                  $count, getMemory());
      }
    }

    unset($fti[$lexemeId]);
  }

  $insert->end();
}

function main() {

  ini_set('memory_limit', '2G');

  $opts = getopt('f');
  $force = isset($opts['f']);

  Log::notice('started');
  if (!$force && Variable::peek(Variable::LOCK_FTI)) {
    OS::errorAndExit('Lock already exists! Use -f to bypass');
  }
  Variable::poke(Variable::LOCK_FTI, '1');
  DB::execute('truncate table FullTextIndex');

  // build a map of stop words
  $stopWordForms = array_flip(DB::getArray(
    'select distinct i.formNoAccent ' .
    'from Lexeme l, InflectedForm i ' .
    'where l.id = i.lexemeId ' .
    'and l.stopWord'));

  // inflectedForm => concatenated lexemeIds
  $ifMap = buildInflectedFormMap();
  $memFti = buildInMemoryFti($ifMap, $stopWordForms);
  traverseInMemoryFti($memFti);

  Variable::clear(Variable::LOCK_FTI);
  Log::notice('finished; peak memory usage %d MB', getMemory(true));
}

main();

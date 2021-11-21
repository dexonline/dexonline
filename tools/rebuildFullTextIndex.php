<?php

require_once __DIR__ . '/../lib/Core.php';

/**
 * Truncates and rebuilds the FullTextIndex. Computes the entire index in
 * memory so that we can output it in primary key order. This makes the
 * inserts much faster.
 */

function getMemory($peak = false) {
  $m = $peak ? memory_get_peak_usage() : memory_get_usage();
  return round($m / 1048576);
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
    $ifMap[$form] = ($ifMap[$form] ?? '') . ',' . $r['lexemeIds'];
  }

  // Make lexemeIds for each form unique. There may be duplicates due to (1)
  // identical forms for different inflections and (2) upper/lowercase.
  foreach ($ifMap as $lexemeId => $str) {
    $str = trim($str, ',');
    $parts = explode(',', $str);
    $parts = array_unique($parts);
    $ifMap[$lexemeId] = implode(',', $parts);
  }

  Log::info('Inflected form map has %d entries | %d MB', count($ifMap), getMemory());
  return $ifMap;
}

/**
 * Speeds up inserts by performing them in bulk.
 */
class BulkInsert {
  private string $query = '';
  private int $queryLimit;
  private int $records = 0;

  function __construct() {
    // Query length is limited only by max_packet_size. That could afford us
    // millions of rows, but we'll put a reasonable cap on that.
    $result = DB::getArrayOfRows('show variables like "max_allowed_packet"');
    $maxPacketSize = (int)$result[0]['Value'];
    $this->queryLimit = min($maxPacketSize * 0.9, 1000000);
  }

  function push(array $row) {
    $this->query .= '(' . implode(',', $row) . '),';

    if (++$this->records % 1000000 == 0) {
      Log::info('%d rows pushed to BulkInsert', $this->records);
    }

    if (strlen($this->query) > $this->queryLimit) {
      $this->flush();
    }
  }

  private function flush() {
    if ($this->query) {
      $s = trim($this->query, ',');
      DB::execute('insert into FullTextIndex values ' . $s);
      // Log::debug(sprintf('Inserted %d rows. | %d MB', $this->records, getMemory()));
      $this->query = '';
    }
  }

  function end() {
    $this->flush(); // call insert with remaining data
  }
}

function extractWords($s) {
  // throw away hidden text and footnotes
  $s = preg_replace('/▶.*◀/sU', '', $s);
  $s = preg_replace('/(?<!\\\\)\{\{.*\}\}/sU', '', $s);

  $s = mb_strtolower($s);
  $s = Str::removeAccents($s);

  return preg_split('/\P{L}+/u', $s, null, PREG_SPLIT_NO_EMPTY);
}

function qsort(SplFixedArray $a, int $begin, int $end) {
  $b = $begin;
  $e = $end;
  $pivot = $a[($begin + $end) / 2];

  while ($b <= $e) {
    while ($a[$b] < $pivot) {
      $b++;
    }
    while ($a[$e] > $pivot) {
      $e--;
    }
    if ($b <= $e) {
      $aux = $a[$b]; $a[$b] = $a[$e]; $a[$e] = $aux;
      $b++;
      $e--;
    }
  }
  if ($begin < $e) {
    qsort($a, $begin, $e);
  }
  if ($b < $end) {
    qsort($a, $b, $end);
  }
}

/**
 * Reads all definitions, creates the index and inserts it.
 */
function scanDefinitions(array &$ifMap, array &$stopWordForms) {
  $size = 0;  // number of records in the SplFixedArray
  $limit = 0; // maximum size of the SplFixedArray
  $defs = 0;  // number of definitions scanned
  $spl = new SplFixedArray(0);

  $dbResult = DB::execute('select id, internalRep from Definition where status = 0');

  foreach ($dbResult as $dbRow) {
    $words = extractWords($dbRow[1]);

    foreach ($words as $position => $word) {
      if (!isset($stopWordForms[$word]) && isset($ifMap[$word])) {
        $lexemeIds = explode(',', $ifMap[$word]);
        foreach ($lexemeIds as $lexemeId) {
          if ($size == $limit) {
            $limit += 1000000;
            $spl->setSize($limit);
          }
          // 23 bits for definitionIds, 22 for lexemeIds, 16 for positions
          $spl[$size++] = ($lexemeId << 38) | ($dbRow[0] << 16) | $position;
        }
      }
    }

    if (++$defs % 50000 == 0) {
      $runTime = DebugInfo::getRunningTimeInMillis() / 1000;
      $speed = round($defs / $runTime);
      Log::info('%d definitions scanned (%d defs/sec) | index size %d | %d MB',
                $defs, $speed, $size, getMemory());
    }
  }
  Log::info('%d definitions scanned | index size %d | %d MB', $defs, $size, getMemory());
  $spl->setSize($size);

  Log::info('Sorting index.');
  qsort($spl, 0, $size - 1);
  Log::info('Index sorted.');

  $insert = new BulkInsert();
  foreach ($spl as $val)  {
    // unpack triplets packed above
    $insert->push([ $val >> 38, $val >> 16 & 0x3fffff, $val & 0xffff ]);
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
  DB::setBuffering(false);
  DebugInfo::disable(); // to prevent logging of huge insert queries

  // build a map of stop words
  $stopWordForms = array_flip(DB::getArray(
    'select distinct i.formNoAccent ' .
    'from Lexeme l, InflectedForm i ' .
    'where l.id = i.lexemeId ' .
    'and l.stopWord'));

  // inflectedForm => concatenated lexemeIds
  $ifMap = buildInflectedFormMap();
  scanDefinitions($ifMap, $stopWordForms);

  Variable::clear(Variable::LOCK_FTI);
  Log::notice('finished; peak memory usage %d MB', getMemory(true));
}

main();

<?php
require_once __DIR__ . '/../lib/Core.php';
ini_set('max_execution_time', '3600');
ini_set('memory_limit', '2G');

$opts = getopt('f');
$force = isset($opts['f']);

Log::notice('started');
if (!$force && Variable::peek(Variable::LOCK_FTI)) {
  OS::errorAndExit('Lock already exists! Use -f to bypass');
}
Variable::poke(Variable::LOCK_FTI, '1');

Log::info('Clearing table FullTextIndex.');
DB::execute('truncate table FullTextIndex');

// Build a map of stop words
$stopWordForms = array_flip(DB::getArray(
  'select distinct i.formNoAccent ' .
  'from Lexeme l, InflectedForm i ' .
  'where l.id = i.lexemeId ' .
  'and l.stopWord'));

// Build a map of inflectedForm => list of (lexemeId, inflectionId) pairs
Log::info('Building inflected form map.');
$dbResult = DB::execute('select distinct formNoAccent, lexemeId, inflectionId from InflectedForm');
$ifMap = [];
foreach ($dbResult as $r) {
  $form = mb_strtolower($r['formNoAccent']);
  $s = isset($ifMap[$form])
     ? ($ifMap[$form] . ',')
     : '';
  $s .= $r['lexemeId'] . ',' . $r['inflectionId'];
  $ifMap[$form] = $s;
}
unset($dbResult);
Log::info('Inflected form map has %d entries.', count($ifMap));
Log::info('Memory used: %d MB', round(memory_get_usage() / 1048576, 1));

// Process definitions
$dbResult = DB::execute('select id, internalRep from Definition where status = 0');
$defsSeen = 0;
$handle = fopen('/tmp/mysql_full_text_index', 'w');

DebugInfo::disable();

foreach ($dbResult as $dbRow) {
  $words = extractWords($dbRow[1]);

  foreach ($words as $position => $word) {
    if (!isset($stopWordForms[$word])) {
      if (array_key_exists($word, $ifMap)) {
        $lexemeList = preg_split('/,/', $ifMap[$word]);
        for ($i = 0; $i < count($lexemeList); $i += 2) {
          fprintf($handle, "%07d %04d %08d %06d\n",
                  $lexemeList[$i],
                  $lexemeList[$i + 1],
                  $dbRow[0],
                  $position);
        }
      }
    }
  }

  if (++$defsSeen % 10000 == 0) {
    $runTime = DebugInfo::getRunningTimeInMillis() / 1000;
    $speed = round($defsSeen / $runTime);
    Log::info("$defsSeen definitions scanned ($speed defs/sec). ");
  }
}

fclose($handle);
$dbResult = null; // mark for data collection
Log::info("$defsSeen definitions scanned.");

Log::info('Sorting temporary file');
OS::execute('sort /tmp/mysql_full_text_index > /tmp/mysql_full_text_index_sorted');

Log::info('Inserting rows');
$insert = new Insert();
$handle = fopen('/tmp/mysql_full_text_index_sorted', 'r');
while (fscanf($handle, '%d %d %d %d', $lexemeId, $inflectionId, $definitionId, $pos) == 4) {
  $s = sprintf('(%d,%d,%d,%d)', $lexemeId, $inflectionId, $definitionId, $pos);
  $insert->push($s);
}

$insert->end();

unlink('/tmp/mysql_full_text_index');
unlink('/tmp/mysql_full_text_index_sorted');

Variable::clear(Variable::LOCK_FTI);
Log::notice('finished; peak memory usage %d MB', round(memory_get_peak_usage() / 1048576, 1));

/***************************************************************************/

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
 * Bulk insert with hints from https://dev.mysql.com/doc/refman/8.0/en/optimizing-innodb-bulk-data-loading.html
 */
class Insert {
  private string $query = '';
  private int $queryLen = 0; // avoid numerous strlen($this->query)
  private int $queryLimit;
  private int $records = 0;

  function __construct() {
    // Query length is limited only by max_packet_size.
    $result = DB::getArrayOfRows('show variables like "max_allowed_packet"');
    $maxPacketSize = (int)$result[0]['Value'];
    $this->queryLimit = $maxPacketSize * 0.9;

    DB::execute('SET unique_checks=0');
    DB::execute('SET autocommit=0');
  }

  function push(string $tuple) {
    if ($this->query) {
      $tuple = ',' . $tuple;
    }
    $this->query .= $tuple;
    $this->queryLen += strlen($tuple);
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
    DB::execute('insert into FullTextIndex values ' . $this->query);
    DB::execute('commit');
    Log::info("Index size: {$this->records} rows.");

    $this->query = '';
    $this->queryLen = 0;
  }

  function end() {
    $this->run(); // call insert with remaining tuples

    DB::execute('SET autocommit=1');
    DB::execute('SET unique_checks=1');
  }
}

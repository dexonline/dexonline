<?php

require_once __DIR__ . '/../lib/Core.php';

/**
 * Updates the FullTextIndex by merging the existing index with data gathered
 * from definitions. Is also able to handle rebuilding the index from scratch.
 * Always uses the FTI columns in key order (the actual table column order may
 * differ for historical reasons).
 */

/**
 * Compares two numerically indexed arrays of equal lenghts.
 */
function cmp(array &$a, array &$b) {
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
 * An iterator over existing records in the FTI table.
 */
class DbIterator implements Iterator {

  private ?PDOStatement $dbResult;
  private array $row; // tuple (lexemeId, definitionId, position, inflectionId) or []
  private int $total;

  function __construct() {
    $this->total = 0;
    DB::setBuffering(false);
    $this->dbResult = DB::execute(
      'select lexemeId, definitionId, position, inflectionId from FullTextIndex ' .
      'order by lexemeId, definitionId, position, inflectionId');
    $this->next();
  }

  function current() {
    return $this->row;
  }

  function key() {
    throw new Exception('Keys are meaningless in a PDO result set.');
  }

  function next() {
    $this->row = $this->dbResult->fetch(PDO::FETCH_NUM) ?: [];
    if (empty($this->row)) {
      // close the connection early so we can start running other queries
      $this->dbResult = null;
    }
    if (++$this->total % 1000000 == 0) {
      Log::info('%d rows merged from the DB FTI. | %d MB',
                $this->total, getMemory());
    }
  }

  function rewind() {
    throw new Exception('Please don\'t attempt to rewind a PDO result set.');
  }

  function valid() {
    return !empty($this->row);
  }

}

/**
 * An iterator over the in-memory data scraped from definitions.
 */
class MapIterator implements Iterator {

  // lexemeId => concatenated (definitionId, position, inflectionId) strings
  private array $map;
  private int $lexemeId;
  private array $data; // lists of tuples expanded from a string in $map
  private int $index; // pointer in $data
  private int $total;

  function __construct(&$map) {
    $this->map = $map;
    ksort($this->map); // ensure lexemeId order

    $this->data = [];
    $this->index = 0;
    $this->total = 0;
    $this->next();
  }

  function current() {
    if (!$this->lexemeId) {
      return false;
    }

    $i = $this->index;
    return [
      $this->lexemeId,
      $this->data[$i][0],
      $this->data[$i][1],
      $this->data[$i][2],
    ];
  }

  function key() {
    throw new Exception('Keys are not supported nor needed.');
  }

  function next() {
    $this->index++;

    if ($this->index >= count($this->data)) {
      if (empty($this->map)) {
        // done
        $this->lexemeId = 0;
      } else {

        // process the next lexemeId
        $this->lexemeId = array_key_first($this->map);
        $tuples = explode('|', $this->map[$this->lexemeId]);
        $this->data = [];
        foreach ($tuples as $tuple) {
          $this->data[] = explode(',', $tuple);
        }
        usort($this->data, 'cmp'); // sort in primary key order
        $this->index = 0;

        // mark key-value pair for garbage collection
        $this->map[$this->lexemeId] = null;
        unset($this->map[$this->lexemeId]);

      }
    }

    if (++$this->total % 1000000 == 0) {
      Log::info('%d rows merged from the in-memory FTI. | %d MB',
                $this->total, getMemory());
    }
  }

  function rewind() {
    throw new Exception('Rewinding is not supported nor needed.');
  }

  function valid() {
    return $this->lexemeId != 0;
  }

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
    // Query length is limited only by max_packet_size.
    $result = DB::getArrayOfRows('show variables like "max_allowed_packet"');
    $maxPacketSize = (int)$result[0]['Value'];
    $this->queryLimit = $maxPacketSize * 0.9;

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
      'insert into FullTextIndex (lexemeId, definitionId, position, inflectionId) ' .
      'values' . $this->query);
    DB::execute('commit');
    Log::info(sprintf('Inserted %d rows. | %d MB', $this->records, getMemory()));
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
 * Bulk delete helper. Not really needed as there is no bulk delete option in
 * SQL, but added for clarity.
 */
class BulkDelete {
  const LIMIT = 100000;
  private array $data = [];
  private int $count = 0;

  function __construct() {
  }

  function push(array $row) {
    $this->data[] = $row;
    $this->count++;

    if (count($this->data) == self::LIMIT) {
      $this->run();
    }
  }

  function run() {
    foreach ($this->data as $row) {
      $query = vsprintf(
        'delete from FullTextIndex ' .
        'where lexemeId = %d ' .
        'and definitionId = %d ' .
        'and position = %d ' .
        'and inflectionId = %d',
        $row);
      DB::execute($query);
    }
    Log::info(sprintf('Deleted %d rows. | %d MB', $this->count, getMemory()));
    $this->data = [];
  }

  function end() {
    $this->run(); // call delete with remaining data
  }
}

/**
 * Builds a map of inflectedForm => concatenated (lexemeId, inflectionId) pairs.
 */
function buildInflectedFormMap() {
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
 * $lexemeId => concatenated ($inflectionId, $definitionId, $position) tuples.
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
          $lexemeList = preg_split('/,/', $ifMap[$word]);
          for ($i = 0; $i < count($lexemeList); $i += 2) {
            $lexemeId = $lexemeList[$i];
            $chunk = $dbRow[0] . ',' . $position . ',' . $lexemeList[$i + 1];
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

/***************************************************************************/

ini_set('memory_limit', '2G');

$opts = getopt('f');
$force = isset($opts['f']);

Log::notice('started');
if (!$force && Variable::peek(Variable::LOCK_FTI)) {
  OS::errorAndExit('Lock already exists! Use -f to bypass');
}
Variable::poke(Variable::LOCK_FTI, '1');

// build a map of stop words
$stopWordForms = array_flip(DB::getArray(
  'select distinct i.formNoAccent ' .
  'from Lexeme l, InflectedForm i ' .
  'where l.id = i.lexemeId ' .
  'and l.stopWord'));

// inflectedForm => concatenated (lexemeId, inflectionId) pairs
$ifMap = buildInflectedFormMap();
$memFti = buildInMemoryFti($ifMap, $stopWordForms);

// Merge the DB and in-memory FTIs. Insert/delete the differences.
$insert = new BulkInsert();
$delete = new BulkDelete();
$dbIter = new DbIterator();
$mapIter = new MapIterator($memFti);

while ($dbIter->valid() && $mapIter->valid()) {
  $dbRow = $dbIter->current();
  $mapRow = $mapIter->current();

  $ord = $dbRow <=> $mapRow;
  if ($ord < 0) {
    $delete->push($dbRow);
    $dbIter->next();
  } else if ($ord == 0) {
    $dbIter->next();
    $mapIter->next();
  } else {
    $insert->push($mapRow);
    $mapIter->next();
  }
}

while ($dbIter->valid()) {
  $delete->push($dbIter->current());
  $dbIter->next();
}


while ($mapIter->valid()) {
  $insert->push($mapIter->current());
  $mapIter->next();
}

$insert->end();
$delete->end();

Variable::clear(Variable::LOCK_FTI);
Log::notice('finished; peak memory usage %d MB', getMemory(true));

<?php

require_once '../../lib/Core.php';

ini_set('memory_limit', '512M');

const MAX_DIFF = 4;
const NUM_ROUNDS = 10;
const NUM_CHOICES = 4;
const INITIAL_POOL_SIZE = 100;
const INITIAL_DISTANCE = 50;

$difficulty = Request::get('d', 0);

// global counter of calls to Levenshtein
$totalAttempts = 0;

// Compute the high and low guess rate for the given difficulty. To do this,
// sort the records by guess rate, compute the slice for the given difficulty,
// and fetch the first and last guess rate of the slice.
$mdCount = Model::factory('MillData')->count();
$loOffset = (int)($mdCount * $difficulty / MAX_DIFF);
$hiOffset = (int)($mdCount * ($difficulty + 1) / MAX_DIFF - 1);
$loRatio = getRatio($loOffset);
$hiRatio = getRatio($hiOffset);

$mds = getRandomRows(NUM_ROUNDS, $loRatio, $hiRatio);

// Hide each record among similar records chosen according to the difficulty.
$resp = [];
foreach ($mds as $md) {
  $resp[] = hide($md, $difficulty);
}

Log::info('Total attempts: %d', $totalAttempts);
header('Content-Type: application/json');
print json_encode($resp);

/*************************************************************************/

/**
 * Returns the $offset smallest ratio in MillData. It's the same as
 *
 *  select * from MillData order by ratio limit 1 offset $offset;
 *
 * , but optimized for speed in MySQL (in MariaDB it was already fast).
 */
function getRatio($offset) {
  $rec = Model::factory('MillData')
    ->select('ratio')
    ->order_by_asc('ratio')
    ->offset($offset)
    ->find_one();
  return $rec->ratio;
}

/**
 * Load NUM_ROUNDS records at random from the given frequencies. We know there
 * are enough records to choose from, since the high and low ratios span at
 * least 1/4 of the data.
 * This does the same thing as "order by rand()", but should be faster.
 */
function getRandomRows($count, $loRatio, $hiRatio) {
  $results = [];
  $offsets = [];

  $numRows = Model::factory('MillData')
    ->where_gte('ratio', $loRatio)
    ->where_lte('ratio', $hiRatio)
    ->count();

  while ($count--) {
    // generate a distinct random number
    do {
      $rnd = rand() % $numRows;
    } while (isset($offsets[$rnd]));
    $offsets[$rnd] = true;

    // fetch just the ID; this is considerably faster in MySQL
    $md = Model::factory('MillData')
      ->select('id')
      ->where_gte('ratio', $loRatio)
      ->where_lte('ratio', $hiRatio)
      ->order_by_asc('id')
      ->offset($rnd)
      ->find_one();

    // fetch the row
    $results[] = MillData::get_by_id($md->id);
  }

  return $results;
}

function hide($md, $difficulty) {
  Log::info("hiding {$md->word} [meaningId={$md->meaningId}] difficulty {$difficulty}");

  // Run a progressively wider approximate search. Increase the Levenshtein
  // distance slowly and number of matches more quickly until we collect
  // enough MillData's. Be more lenient than the regular Levenshtein search to
  // reduce the number of unsuccessful attempts.
  $poolSize = INITIAL_POOL_SIZE;
  $dist = INITIAL_DISTANCE;
  $attempts = 0;
  do {
    $attempts++;
    $forms = Levenshtein::closest($md->word, $poolSize, $dist);

    // cross-reference the forms with the MillData table
    $mds = Model::factory('MillData')
      ->where_not_equal('word', $md->word)
      ->where_in('word', $forms)
      ->where_raw("(posMask & {$md->posMask})")
      ->order_by_expr('rand()')
      ->limit(NUM_CHOICES - 1)
      ->find_many();

    $poolSize = (int)($poolSize * 1.5);
    $dist = (int)($dist * 1.5);
  } while (count($mds) < NUM_CHOICES - 1);

  $GLOBALS['totalAttempts'] += $attempts;
  Log::info('choices found after %d attempts:', $attempts);
  foreach ($mds as $other) {
    Log::info("* {$other->word} [meaningId={$other->meaningId}]");
  }

  // Add the original md and shuffle the array
  $mds[] = $md;
  shuffle($mds);

  $answer = 0;
  while ($mds[$answer]->id != $md->id) {
    $answer++;
  }

  $results = [
    'choices' => [],
    'answer' => $answer,
    'millDataId' => $md->id,
  ];
  foreach ($mds as $md) {
    $results['choices'][] = [
      'word' => $md->word,
      'html' => HtmlConverter::convert($md),
    ];
  }

  return $results;
}

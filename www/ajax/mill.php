<?php

require_once '../../lib/Core.php';

const MAX_DIFF = 4;
const NUM_ROUNDS = 10;
const NUM_CHOICES = 4;
const INITIAL_POOL_SIZE = 30;

$difficulty = Request::get('d', 0);

// Compute the high and low guess rate for the given difficulty. To do this,
// sort the records by guess rate, compute the slice for the given difficulty,
// and fetch the first and last guess rate of the slice.
$mdCount = Model::factory('MillData')->count();
$loOffset = (int)($mdCount * $difficulty / MAX_DIFF);
$hiOffset = (int)($mdCount * ($difficulty + 1) / MAX_DIFF - 1);
$loRatio = Model::factory('MillData')
  ->order_by_asc('ratio')
  ->offset($loOffset)
  ->find_one();
$hiRatio = Model::factory('MillData')
  ->order_by_asc('ratio')
  ->offset($hiOffset)
  ->find_one();

// Load NUM_ROUNDS records at random from the given frequencies. We know there
// are enough records to choose from, since the high and low ratios span at
// least 1/4 of the data.
$mds = Model::factory('MillData')
  ->where_gte('ratio', $loRatio->ratio)
  ->where_lte('ratio', $hiRatio->ratio)
  ->order_by_expr('rand()')
  ->limit(NUM_ROUNDS)
  ->find_many();

// Hide each record among similar records chosen according to the difficulty.
$resp = [];
foreach ($mds as $md) {
  $resp[] = hide($md, $difficulty);
}

header('Content-Type: application/json');
print json_encode($resp);

/*************************************************************************/

function hide($md, $difficulty) {
  Log::info("hiding {$md->word} [meaningId={$md->meaningId}] difficulty {$difficulty}");

  // Run a progressively wider approximate search. Increase the Levenshtein
  // distance slowly and number of matches more quickly until we collect
  // enough MillData's. Be more lenient than the regular Levenshtein search to
  // reduce the number of unsuccessful attempts.
  $poolMultiplier = $distMultiplier = 1;
  do {
    $forms = Levenshtein::closest(
      $md->word,
      $poolMultiplier * INITIAL_POOL_SIZE,
      $distMultiplier * Levenshtein::MAX_DISTANCE
    );

    // cross-reference the forms with the MillData table
    $mds = Model::factory('MillData')
      ->where_not_equal('word', $md->word)
      ->where_in('word', $forms)
      ->where_raw("(posMask & {$md->posMask})")
      ->order_by_expr('rand()')
      ->limit(NUM_CHOICES - 1)
      ->find_many();
    $poolMultiplier++;
    $distMultiplier += 0.2;
  } while (count($mds) < NUM_CHOICES - 1);
  Log::info('choices found after %d attempts:', $poolMultiplier - 1);
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

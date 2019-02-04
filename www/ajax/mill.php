<?php

require_once '../../lib/Core.php';

// word frequency for each difficulty
const DIFF_FREQUENCY = [
  1 => [ 0.90, 1.00 ],
  2 => [ 0.70, 0.90 ],
  3 => [ 0.45, 0.70 ],
  4 => [ 0.00, 0.45 ],
];

// number of similar words to choose for each difficulty
const POOL_SIZE = [
  1 => 200,
  2 => 100,
  3 => 50,
  4 => 20,
];

$difficulty = Request::get('d', 1);

$mainDef = Model::factory('DefinitionSimple')
  ->table_alias('d')
  ->select('d.*')
  ->join('Lexeme', ['d.lexicon', '=', 'l.formNoAccent'], 'l')
  ->where_gt('l.frequency', DIFF_FREQUENCY[$difficulty][0])
  ->where_lte('l.frequency', DIFF_FREQUENCY[$difficulty][1])
  ->order_by_expr('rand()')
  ->find_one();
$answer = rand(1, 4);

$otherDefs = findSimilar($mainDef->lexicon, POOL_SIZE[$difficulty]);

$choices = [];
for ($i = 1; $i <= 4; $i++) {
  $d = ($i == $answer) ? $mainDef : array_pop($otherDefs);
  $choices[$i] = [
    'term' => $d->lexicon,
    'text' => $d->definition,
  ];
}

$resp = [
  'defId' => $mainDef->id,
  'answer' => $answer,
  'choices' => $choices,
];

header('Content-Type: application/json');
print json_encode($resp);

/*************************************************************************/

function findSimilar($query, $count) {
  $defs = [];

  // run a wider approximate search; we don't want accurate results, just similar enough
  $multiplier = 1;
  do {
    $forms = Levenshtein::closest($query, $count, $multiplier * Levenshtein::MAX_DISTANCE);
    $defs = Model::factory('DefinitionSimple')
      ->where_in('lexicon', $forms)
      ->find_many();
    $multiplier++;
  } while (count($forms) < $count);

  // remove exact matches
  $defs = array_filter($defs, function($d) use ($query) {
    return $d->lexicon != $query;
  });

  // pad with random definitions so we have enough alternatives
  while (count($defs) < 3) {
    $defs[] = Model::factory('DefinitionSimple')
      ->where_not_equal('lexicon', $query)
      ->order_by_expr('rand()')
      ->find_one();
  }

  shuffle($defs);
  $defs = array_slice($defs, 0, 3);

  return $defs;
}

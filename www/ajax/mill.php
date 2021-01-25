<?php

require_once '../../lib/Core.php';

// word frequency for each difficulty
const DIFF_FREQUENCY = [
  1 => [ 0.94, 1.00 ],
  2 => [ 0.86, 0.94 ],
  3 => [ 0.75, 0.86 ],
  4 => [ 0.00, 0.75 ],
];

// number of similar words to choose for each difficulty
const POOL_SIZE = [
  1 => 200,
  2 => 100,
  3 => 50,
  4 => 20,
];

$difficulty = Request::get('d', 1);

$meaning = Model::factory('Meaning')
  ->table_alias('m')
  ->select('m.*')
  ->select('t.description')
  ->join('Tree', ['m.treeId', '=', 't.id'], 't')
  ->join('TreeEntry', ['t.id', '=', 'te.treeId'], 'te')
  ->join('Entry', ['te.entryId', '=', 'e.id'], 'e')
  ->join('EntryLexeme', ['e.id', '=', 'el.entryId'], 'el')
  ->raw_join(
    'left join Lexeme',
    'el.lexemeId = l.id and l.formNoAccent = t.description',
    'l',
    [])
  ->where('m.parentId', 0)
  ->where('m.type', Meaning::TYPE_MEANING)
  ->where_not_equal('m.internalRep', '')
  ->where('t.status', Tree::ST_VISIBLE)
  ->where('e.structStatus', Entry::STRUCT_STATUS_DONE)
  ->where('el.main', 1)
  ->where_gte('l.frequency', DIFF_FREQUENCY[$difficulty][0])
  ->where_lte('l.frequency', DIFF_FREQUENCY[$difficulty][1])
  ->order_by_expr('rand()')
  ->find_one();
//  ->count();
$answer = rand(1, 4);

$otherMeanings = findSimilar($meaning->description, POOL_SIZE[$difficulty]);

$choices = [];
for ($i = 1; $i <= 4; $i++) {
  $m = ($i == $answer) ? $meaning : array_pop($otherMeanings);
  $choices[$i] = [
    'term' => $m->description,
    'html' => HtmlConverter::convert($m),
  ];
}

$resp = [
  'meaningId' => $meaning->id,
  'answer' => $answer,
  'choices' => $choices,
];

header('Content-Type: application/json');
print json_encode($resp);

/*************************************************************************/

function findSimilar($query, $count) {
  $meanings = [];

  // run a wider approximate search; we don't want accurate results, just similar enough
  $multiplier = 1;
  do {
    $forms = Levenshtein::closest($query, $count, $multiplier * Levenshtein::MAX_DISTANCE);

    $meanings = Model::factory('Meaning')
      ->table_alias('m')
      ->select('m.*')
      ->select('t.description')
      ->join('Tree', ['m.treeId', '=', 't.id'], 't')
      ->join('TreeEntry', ['t.id', '=', 'te.treeId'], 'te')
      ->join('Entry', ['te.entryId', '=', 'e.id'], 'e')
      ->where('m.parentId', 0)
      ->where('m.type', Meaning::TYPE_MEANING)
      ->where_not_equal('m.internalRep', '')
      ->where('t.status', Tree::ST_VISIBLE)
      ->where_in('t.description', $forms)
      ->where('e.structStatus', Entry::STRUCT_STATUS_DONE)
      ->order_by_expr('rand()')
      ->find_many();

    $multiplier++;
  } while (count($forms) < $count);

  // remove exact matches
  $meanings = array_filter($meanings, function($m) use ($query) {
    return $m->description != $query;
  });

  // pad with random meanings so we have enough alternatives
  while (count($meanings) < 3) {
    $meanings[] = Model::factory('Meaning')
      ->table_alias('m')
      ->select('m.*')
      ->select('t.description')
      ->join('Tree', ['m.treeId', '=', 't.id'], 't')
      ->join('TreeEntry', ['t.id', '=', 'te.treeId'], 'te')
      ->join('Entry', ['te.entryId', '=', 'e.id'], 'e')
      ->where('m.parentId', 0)
      ->where('m.type', Meaning::TYPE_MEANING)
      ->where_not_equal('m.internalRep', '')
      ->where('t.status', Tree::ST_VISIBLE)
      ->where_not_equal('t.description', $query)
      ->where('e.structStatus', Entry::STRUCT_STATUS_DONE)
      ->order_by_expr('rand()')
      ->find_one();
  }

  shuffle($meanings);
  $meanings = array_slice($meanings, 0, 3);

  return $meanings;
}

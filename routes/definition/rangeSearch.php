<?php

User::mustHave(User::PRIV_EDIT);

const DEBUG = 0;

$word_start = Request::get('i');
$word_end = Request::get('e');
$sourceIds = Request::getArray('s');

$searchResults = null;
if ($word_start && $word_end && $sourceIds) {
  $definitions = Definition::getListOfWordsFromSources($word_start, $word_end, $sourceIds);
  $searchResults = SearchResult::mapDefinitionArray($definitions);
}

if (DEBUG) {
  echo "<pre>";
  print_r($sourceIds);
  //print_r($definitions);
  echo "</pre>";
}

Smart::assign([
  'results' => $searchResults,
  's' => array_flip($sourceIds),
  'i' => $word_start,
  'e' => $word_end,
]);
Smart::display('definition/rangeSearch.tpl');

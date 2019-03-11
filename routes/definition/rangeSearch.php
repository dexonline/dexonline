<?php

User::mustHave(User::PRIV_EDIT);

const DEBUG = 0;

$word_start = Request::get('i');
$word_end = Request::get('e');
$sources = Request::get('s');

$searchResults = null;
if ($word_start && $word_end && $sources) {
  $definitions = Definition::getListOfWordsFromSources($word_start, $word_end, $sources);
  $searchResults = SearchResult::mapDefinitionArray($definitions);
}

if(DEBUG) {
    echo "<pre>";
    print_r($sources);
    //print_r($definitions);
    echo "</pre>";
}


$s = "[" . ($sources ? implode($sources, ",") : "") . "]";

Smart::assign([
  'results' => $searchResults,
  's' => $s,
  'i' => $word_start,
  'e' => $word_end,
]);
Smart::display('definition/rangeSearch.tpl');

<?php

require_once("../phplib/Core.php"); 
User::mustHave(User::PRIV_EDIT);

define('DEBUG', 0);

require_once("../phplib/Core.php");
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

SmartyWrap::assign('results', $searchResults);
SmartyWrap::assign('s', $s);
SmartyWrap::assign('i', $word_start);
SmartyWrap::assign('e', $word_end);

SmartyWrap::display('cuvinte.tpl');

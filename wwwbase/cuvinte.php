<?php

util_assertModerator(PRIV_ADMIN);

define('DEBUG', 0);

require_once("../phplib/util.php");
$word_start = util_getRequestParameter('i');
$word_end = util_getRequestParameter('e');
$sources = util_getRequestParameter('s');

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


$s = "[" . ($sources ? join($sources, ",") : "") . "]";

SmartyWrap::assign('results', $searchResults);
SmartyWrap::assign('searchCuv', true);
SmartyWrap::assign('s', $s);
SmartyWrap::assign('i', $word_start);
SmartyWrap::assign('e', $word_end);

SmartyWrap::displayCommonPageWithSkin('cuvinte.ihtml');

?>

<?php
require_once("../phplib/util.php");

$default_order = array(CRIT_CHARS => SORT_DESC,
		       CRIT_WORDS => SORT_DESC,
		       CRIT_NICK  => SORT_ASC,
		       CRIT_DATE  => SORT_DESC);

// Parse or initialize the GET/POST arguments
$scriptParameters = array("crit" => CRIT_CHARS /* sort criteria */,
                           "ord" => $default_order[CRIT_CHARS] /* sort order */,
                         "start" => 0 /* first user's index */);
extract($scriptParameters, EXTR_PREFIX_ALL, "req");
extract($_REQUEST, EXTR_PREFIX_ALL, "req"); 

// Sanitize the arguments
if ($req_crit != CRIT_CHARS && $req_crit != CRIT_WORDS &&
    $req_crit != CRIT_NICK && $req_crit != CRIT_DATE) {
  $req_crit = CRIT_CHARS;
}
if ($req_ord != SORT_ASC && $req_ord != SORT_DESC) {
  $req_ord = $default_order[$req_crit];
}

define("COUNT", 20); // This won't be configurable for now, 20 users per page

// Don't let users specify an in-between page start.
$req_start -= ($req_start % COUNT);

$topEntries = TopEntry::getTopData($req_crit, $req_ord);
$num_rows = count($topEntries);

// Compute the start of the previous and next pages, if applicable
$prev_start = ($req_start > 0) ? $req_start - COUNT : -1;
$next_start = ($req_start + COUNT < $num_rows) ? $req_start + COUNT : -1;
$page = array_slice($topEntries, $req_start, COUNT);

// Set up the values for the links in the column titles. The template may
// or may not use them
$title_links = array();

foreach (array(CRIT_CHARS, CRIT_WORDS, CRIT_NICK, CRIT_DATE) as $c) {
  if ($c == $req_crit) {
    // Clicking on the column would revert the order
    $o = ($req_ord == SORT_ASC) ? SORT_DESC : SORT_ASC;
  } else {
    // Clicking on the column would change the criterion
    $o = $default_order[$c];
  }
  $title_links[$c] = $o;
}

// Set up Smarty data
smarty_assign('data', $page);
smarty_assign('crit', $req_crit);
smarty_assign('ord', $req_ord);
smarty_assign('start', $req_start);
smarty_assign('count', COUNT);
smarty_assign('prev_start', $prev_start);
smarty_assign('next_start', $next_start);
smarty_assign('title_links', $title_links);
smarty_assign('page_title', 'DEX online - Topul voluntarilor');

setlocale (LC_ALL, 'ro_RO');
smarty_displayCommonPageWithSkin('top.ihtml');
?>

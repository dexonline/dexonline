<?php

require_once("../phplib/util.php");
$date = util_getRequestParameter('d');
$type = util_getRequestParameter('t');

# RSS stuff - could be separated from the rest
# TODO optimize & factorize
# TODO create new template for WotD stuff
if ($type == 'rss') {
    $words = WordOfTheDay::getRSSWotD();
    $results = array();
    foreach ($words as $w) {
        $item = array();
        $ts = strtotime($w->displayDate);
        $defId = WordOfTheDayRel::getRefId($w->id);
        $def = Definition::get("id = '$defId' and status = 0");
        $item['title'] = $def->lexicon;
        $item['description'] = $def->htmlRep;
        $item['pubDate'] = date('D, d M Y H:i:s', $ts) . ' EEST';
        $item['link'] = 'http://' . $_SERVER['HTTP_HOST'] . '/cuvantul-zilei/' . date('Y/m/d', $ts);

        $results[] = $item;
    }

    header("Content-type: text/xml");
    smarty_assign('rss_title', 'Cuvântul zilei');
    smarty_assign('rss_link', 'http://' . $_SERVER['HTTP_HOST'] . '/rss/cuvantul-zilei/');
    smarty_assign('rss_description', 'Doza zilnică de cuvinte propuse de DEXonline!');
    smarty_assign('rss_pubDate', date('D, d M Y H:i:s') . ' EEST');
    smarty_assign('results', $results);
    smarty_displayWithoutSkin('common/rss.ixml');
    exit;
}

# get today's date
# check if there's defined a wotd
# if yes return it
# if not randomly choose one
# save it to DB (actually save the today's date)
# and return it

$wotd = new WordOfTheDay();

if ($date) {
    $currentDate = strtotime($date);
    $fdate = date("Y-m-d", $currentDate);
    $titleDate = " ({$fdate})";
    smarty_assign('fdate', $fdate);
    // wotd navigator
    $pre = 'cuvantul-zilei/';
    $prev = strtotime("yesterday", $currentDate);
    if ($prev > strtotime("2011/05/01")) {
        $prevday = $pre . date("Y/m/d", $prev);
        smarty_assign('prevday', $prevday);
    }
    $next = strtotime("tomorrow", $currentDate);
    if ($next < time()) {
        $nextday = $pre . date("Y/m/d", $next);
        smarty_assign('nextday', $nextday);
    }
    $id = $wotd->getOldWotD($fdate);
}
else {
    $titleDate = "";
    $id = $wotd->getTodaysWord();
    if (!$id) {
        $wotd->updateTodaysWord();
        $id = $wotd->getTodaysWord();
    }
#archive
#    $words = WordOfTheDay::getArchiveWotD();
#    smarty_assign('words', $words);
}

$defId = WordOfTheDayRel::getRefId($id);
$def = Definition::get("id = '$defId' and status = 0");
$cuv = ($def && $def->lexicon) ? $def->lexicon : '';

smarty_assign('page_title', "Cuvântul zilei{$titleDate}: {$cuv}");
smarty_assign('page_keywords', "Cuvântul zilei, {$cuv}, dexonline, DEX online, Cuvântul zilei{$titleDate}");
smarty_assign('page_description', "Cuvântul zilei{$titleDate} de la dexonline");

if ($type == 'url') {
    smarty_assign('wwwRoot', 'http://' . $_SERVER['HTTP_HOST'] . '/');
    smarty_assign('title', $def->lexicon);
    smarty_assign('today', date('Y/m/d'));
    smarty_displayWithoutSkin('common/bits/wotdurl.ihtml');
}
else {
    smarty_assign('defId', $defId);
    smarty_assign('searchType', SEARCH_WOTD);
    $definitions = array();
    if ($def) {
        $definitions[] = $def;
    } elseif ($date && (time() < strtotime($date))) {
        header("Location: /cuvantul-zilei");
    }
    else {
        flash_add("Eroare: momentan „Cuvîntul zilei” nu funcționează.");
    }
    $searchResults = SearchResult::mapDefinitionArray($definitions);
    smarty_assign('results', $searchResults);
    smarty_assign('wotd', 1);
    smarty_displayCommonPageWithSkin('search.ihtml');
}

?>

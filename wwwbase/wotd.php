<?php

require_once("../phplib/util.php");
require_once("../phplib/modelObjects.php");
util_assertNotMirror();
$date = util_getRequestParameter('d');
$type = util_getRequestParameter('t');

# get today's date
# check if there's defined a wotd
# if yes return it
# if not randomly choose one
# save it to DB (actually save the today's date)
# and return it
#

$wotd = new WordOfTheDay();
if ($date) {
    $fdate = date("Y-m-d", strtotime($date));
    smarty_assign('fdate', $fdate);
    $id = $wotd->getOldWotD($fdate);
}
else {
    $id = $wotd->getTodaysWord();
    if (!$id) {
        $wotd->updateTodaysWord();
        $id = $wotd->getTodaysWord();
    }
}

$defId = WordOfTheDayRel::getRefId($id);
$def = Definition::get("id = '$defId' and status = 0"); 

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
    } else {
        session_setFlash("Eroare: momentan „Cuvîntul zilei” nu func?ionează.");
    }
    $searchResults = SearchResult::mapDefinitionArray($definitions);
    smarty_assign('results', $searchResults);
    smarty_assign('wotd', 1);
    smarty_displayCommonPageWithSkin('search.ihtml');
}

?>

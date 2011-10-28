<?php
require_once("../phplib/util.php");
require_once("../phplib/ads/adsModule.php");

// Display a custom ad 50% of the times
if (rand(0, 99) < 50) {
  AdsModule::runAllModules(null, null);
}

smarty_assign('page_title', 'Dicționar explicativ al limbii române');
smarty_assign('onHomePage', '1');
smarty_assign('letters', preg_split('//u', 'aăâbcdefghiîjklmnopqrsștțuvwxyz'));
smarty_assign('words_total', util_formatNumber(Definition::getWordCount(), 0));
smarty_assign('words_last_month', util_formatNumber(Definition::getWordCountLastMonth(), 0));

/* WotD part */
$wotd = new WordOfTheDay();
$titleDate = "";
$id = $wotd->getTodaysWord();
if (!$id) {
    $wotd->updateTodaysWord();
    $id = $wotd->getTodaysWord();
}
$defId = WordOfTheDayRel::getRefId($id);
$def = Definition::get("id = '$defId' and status = 0");
smarty_assign('title', $def->lexicon);
smarty_assign('today', date('Y/m/d'));

smarty_displayPageWithSkin('index.ihtml');
?>

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
$wotd = WordOfTheDay::getTodaysWord();
if (!$wotd) {
  WordOfTheDay::updateTodaysWord();
  $wotd = WordOfTheDay::getTodaysWord();
}
$wotd->ensureThumbnail();
$defId = WordOfTheDayRel::getRefId($wotd->id);
$def = Model::factory('Definition')->where('id', $defId)->where('status', ST_ACTIVE)->find_one();
smarty_assign('thumbUrl', $wotd->getThumbUrl());
smarty_assign('title', $def->lexicon);
smarty_assign('today', date('Y/m/d'));

/* WotM part */
$wotm = WordOfTheMonth::getCurrentWotM();
$wotm->ensureThumbnail();
$def = Model::factory('Definition')->where('id', $wotm->definitionId)->where('status', ST_ACTIVE)->find_one();
smarty_assign('thumbUrlM', $wotm->getThumbUrl());
smarty_assign('articol', $wotm->article);
smarty_assign('titleM', $def->lexicon);
smarty_assign('todayM', date('Y/m'));

smarty_displayPageWithSkin('index.ihtml');
?>

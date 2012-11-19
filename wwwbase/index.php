<?php
require_once("../phplib/util.php");
require_once("../phplib/ads/adsModule.php");

// Display a custom ad 50% of the times
if (rand(0, 99) < 50) {
  AdsModule::runAllModules(null, null);
}

SmartyWrap::assign('page_title', 'Dicționar explicativ al limbii române');
SmartyWrap::assign('onHomePage', '1');
SmartyWrap::assign('letters', preg_split('//u', 'aăâbcdefghiîjklmnopqrsștțuvwxyz'));
SmartyWrap::assign('words_total', util_formatNumber(Definition::getWordCount(), 0));
SmartyWrap::assign('words_last_month', util_formatNumber(Definition::getWordCountLastMonth(), 0));

/* WotD part */
$wotd = WordOfTheDay::getTodaysWord();
if (!$wotd) {
  WordOfTheDay::updateTodaysWord();
  $wotd = WordOfTheDay::getTodaysWord();
}
$wotd->ensureThumbnail();
$defId = WordOfTheDayRel::getRefId($wotd->id);
$def = Model::factory('Definition')->where('id', $defId)->where('status', ST_ACTIVE)->find_one();
SmartyWrap::assign('thumbUrl', $wotd->getThumbUrl());
SmartyWrap::assign('title', $def->lexicon);
SmartyWrap::assign('today', date('Y/m/d'));

/* WotM part */
$wotm = WordOfTheMonth::getCurrentWotM();
$wotm->ensureThumbnail();
$def = Model::factory('Definition')->where('id', $wotm->definitionId)->where('status', ST_ACTIVE)->find_one();
SmartyWrap::assign('thumbUrlM', $wotm->getThumbUrl());
SmartyWrap::assign('articol', $wotm->article);
SmartyWrap::assign('titleM', $def->lexicon);
SmartyWrap::assign('todayM', date('Y/m'));

SmartyWrap::displayPageWithSkin('index.ihtml');
?>

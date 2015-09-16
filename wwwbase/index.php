<?php
require_once("../phplib/util.php");
require_once("../phplib/ads/adsModule.php");

// Display a custom ad 50% of the times
if (rand(0, 99) < 50) {
  AdsModule::runAllModules(null, null);
}

$widgets = Preferences::getWidgets(session_getUser());
$numEnabledWidgets = array_reduce($widgets, function($result, $w) { return $result + $w['enabled']; });
$wordCount = Definition::getWordCount();
$wordCountRough = $wordCount - ($wordCount % 10000);

SmartyWrap::assign('onHomePage', '1');
SmartyWrap::assign('letters', preg_split('//u', 'aăâbcdefghiîjklmnopqrsștțuvwxyz'));
SmartyWrap::assign('words_total', util_formatNumber($wordCount, 0));
SmartyWrap::assign('words_rough', util_formatNumber($wordCountRough, 0));
SmartyWrap::assign('words_last_month', util_formatNumber(Definition::getWordCountLastMonth(), 0));
SmartyWrap::assign('widgets', $widgets);
SmartyWrap::assign('numEnabledWidgets', $numEnabledWidgets);

/* WotD part */
$wotd = WordOfTheDay::getTodaysWord();
if (!$wotd) {
  WordOfTheDay::updateTodaysWord();
  $wotd = WordOfTheDay::getTodaysWord();
}
$defId = WordOfTheDayRel::getRefId($wotd->id);
$def = Model::factory('Definition')->where('id', $defId)->where('status', ST_ACTIVE)->find_one();
SmartyWrap::assign('thumbUrl', $wotd->getThumbUrl());
SmartyWrap::assign('title', $def->lexicon);
SmartyWrap::assign('today', date('Y/m/d'));

/* WotM part */
$wotm = WordOfTheMonth::getCurrentWotM();
$def = Model::factory('Definition')->where('id', $wotm->definitionId)->where('status', ST_ACTIVE)->find_one();
SmartyWrap::assign('thumbUrlM', $wotm->getThumbUrl());
SmartyWrap::assign('articol', $wotm->article);
SmartyWrap::assign('titleM', $def->lexicon);
SmartyWrap::assign('todayM', date('Y/m'));

$page = Config::get('global.aprilFoolsDay') ? 'index-afd.tpl' : 'index.tpl';
SmartyWrap::displayPageWithSkin($page);
?>

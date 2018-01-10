<?php
require_once("../phplib/Core.php");
require_once("../phplib/ads/adsModule.php");

// Display a custom ad 50% of the times
if (rand(0, 99) < 50) {
  AdsModule::runAllModules(null, null);
}

$widgets = Preferences::getWidgets(User::getActive());
$numEnabledWidgets = array_reduce($widgets, function($result, $w) { return $result + $w['enabled']; });

SmartyWrap::assign('pageType', 'home');
SmartyWrap::assign('letters', preg_split('//u', 'aăâbcdefghiîjklmnopqrsștțuvwxyz'));
SmartyWrap::assign('wordsTotal', Definition::getWordCount());
SmartyWrap::assign('wordsLastMonth', Definition::getWordCountLastMonth());
SmartyWrap::assign('widgets', $widgets);
SmartyWrap::assign('numEnabledWidgets', $numEnabledWidgets);

/* WotD part */
$wotd = WordOfTheDay::getTodaysWord();
if (!$wotd) {
  WordOfTheDay::updateTodaysWord();
  $wotd = WordOfTheDay::getTodaysWord();
}
if (!$wotd) {
  $wotd = Model::factory('WordOfTheDay')->create(); // generic WotD
}
$defId = WordOfTheDayRel::getRefId($wotd->id);
$def = Model::factory('Definition')->where('id', $defId)->where('status', Definition::ST_ACTIVE)->find_one();
SmartyWrap::assign('thumbUrl', $wotd->getMediumThumbUrl());
SmartyWrap::assign('wotdDef', $def);
SmartyWrap::assign('today', date('Y/m/d'));

/* WotM part */
$wotm = WordOfTheMonth::getCurrentWotM();
if (!$wotm) {
  $wotm = Model::factory('WordOfTheMonth')->create(); // generic WotM
}
$def = Model::factory('Definition')->where('id', $wotm->definitionId)->where('status', Definition::ST_ACTIVE)->find_one();
SmartyWrap::assign('thumbUrlM', $wotm->getMediumThumbUrl());
SmartyWrap::assign('articol', $wotm->article);
SmartyWrap::assign('wotmDef', $def);
SmartyWrap::assign('todayM', date('Y/m'));

$page = 'index.tpl';
SmartyWrap::display($page);
?>

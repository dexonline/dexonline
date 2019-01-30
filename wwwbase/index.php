<?php
require_once '../phplib/Core.php';

$widgets = Preferences::getWidgets(User::getActive());
$numEnabledWidgets = array_reduce($widgets, function($result, $w) { return $result + $w['enabled']; });

SmartyWrap::assign([
  'pageType' => 'home',
  'wordsTotal' => Definition::getWordCount(),
  'wordsLastMonth' => Definition::getWordCountLastMonth(),
  'widgets' => $widgets,
  'numEnabledWidgets' => $numEnabledWidgets,
]);

/* WotD part */
$wotd = WordOfTheDay::getTodaysWord();
if (!$wotd) {
  $wotd = WordOfTheDay::updateTodaysWord();
}
if (!$wotd) {
  $wotd = Model::factory('WordOfTheDay')->create(); // generic WotD
}
$def = Definition::get_by_id_status($wotd->definitionId, Definition::ST_ACTIVE);
SmartyWrap::assign([
  'thumbUrl' => $wotd->getMediumThumbUrl(),
  'wotdDef' => $def,
  'today' => date('Y/m/d'),
]);

/* WotM part */
$wotm = WordOfTheMonth::getCurrentWotM();
if (!$wotm) {
  $wotm = Model::factory('WordOfTheMonth')->create(); // generic WotM
}
$def = Model::factory('Definition')->where('id', $wotm->definitionId)->where('status', Definition::ST_ACTIVE)->find_one();
SmartyWrap::assign([
  'thumbUrlM' => $wotm->getMediumThumbUrl(),
  'articol' => $wotm->article,
  'wotmDef' => $def,
  'todayM' => date('Y/m'),
]);

SmartyWrap::display('index.tpl');

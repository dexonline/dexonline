<?php
require_once '../lib/Core.php';

$widgets = Preferences::getWidgets(User::getActive());
$numEnabledWidgets = array_reduce($widgets, function($result, $w) { return $result + $w['enabled']; });

Smart::assign([
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
Smart::assign([
  'thumbUrl' => $wotd->getMediumThumbUrl(),
  'wotdDef' => $def,
]);

/* WotM part */
$wotm = WordOfTheMonth::getCurrentWotM();
if (!$wotm) {
  $wotm = Model::factory('WordOfTheMonth')->create(); // generic WotM
}
$def = Model::factory('Definition')->where('id', $wotm->definitionId)->where('status', Definition::ST_ACTIVE)->find_one();
Smart::assign([
  'thumbUrlM' => $wotm->getMediumThumbUrl(),
  'articol' => $wotm->article,
  'wotmDef' => $def,
]);

Smart::display('index.tpl');

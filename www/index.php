<?php
require_once '../lib/Core.php';

$widgets = Preferences::getWidgets(User::getActive());

Smart::assign([
  'pageType' => 'home',
  'wordsTotal' => Definition::getWordCount(),
  'wordsLastMonth' => Definition::getWordCountLastMonth(),
  'widgets' => $widgets,
]);

/* WotD part */
$wotd = WordOfTheDay::getTodaysWord();
if (!$wotd) {
  $wotd = WordOfTheDay::updateTodaysWord();
}
if (!$wotd) {
  $wotd = Model::factory('WordOfTheDay')->create(); // generic WotD
}
$wotdDef = Definition::get_by_id_status($wotd->definitionId, Definition::ST_ACTIVE);
Smart::assign([
  'thumbUrl' => $wotd->getMediumThumbUrl(),
  'wotdDef' => $wotdDef,
]);

/* WotM part */
$wotm = WordOfTheMonth::getCurrentWotM();
if (!$wotm) {
  $wotm = Model::factory('WordOfTheMonth')->create(); // generic WotM
}
$wotmDef = Definition::get_by_id_status($wotm->definitionId, Definition::ST_ACTIVE);
Smart::assign([
  'thumbUrlM' => $wotm->getMediumThumbUrl(),
  'articleTitle' => $wotm->article,
  'wotmDef' => $wotmDef,
]);

Smart::display('index.tpl');

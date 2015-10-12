<?php

require_once("../phplib/util.php");

define('NO_OF_WOTD_IN_LIST', 4);

$year = util_getRequestIntParameterWithDefault('y', date('Y'));
$month = util_getRequestIntParameterWithDefault('m', date('m'));
$day = util_getRequestIntParameterWithDefault('d', date('d'));

$timestamp = mktime(0, 0, 0, $month, $day, $year);

$prevWotds = WordOfTheDay::getPreviousYearsWotds($month, $day);
$wotds = [];
foreach ($prevWotds as $w) {
  $currentYear = substr($w->displayDate, 0, 4);
  if (count($wotds) >= NO_OF_WOTD_IN_LIST) break;
  if ($currentYear != $year) {
    $defId = WordOfTheDayRel::getRefId($w->id);
    $def = Model::factory('Definition')->where('id', $defId)->where('status', Definition::ST_ACTIVE)->find_one();

    $entry = [];
    $entry['year'] = $currentYear;
    $entry['href'] = "/cuvantul-zilei/$currentYear/$month/$day";
    $entry['img'] = $w->getThumbUrl();
    $entry['word'] = $def->lexicon;
    $entry['tip'] = $w->description;
    $wotds[] = $entry;
  }
}

SmartyWrap::assign('timestamp', $timestamp);
SmartyWrap::assign('wotds', $wotds);

SmartyWrap::displayWithoutSkin('bits/wotdPreviousYears.tpl');
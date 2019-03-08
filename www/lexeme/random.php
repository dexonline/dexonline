<?php

const MIN_COUNT = 10;
const MAX_COUNT = 2500;
const DEFAULT_COUNT = 100;

const DEFAULT_SHOW_SOURCE = false;
const DEFAULT_SHOW_SKIN = true;
const SOURCE_PART = ', surse';

const QUERY =
  'select cuv %s ' .
  'from RandomWord ' .
  'order by rand() ' .
  'limit %d';

$count = Request::getInRange('count', DEFAULT_COUNT, MIN_COUNT, MAX_COUNT);

$showSource = Request::get('source', DEFAULT_SHOW_SOURCE);
$skin = Request::get('skin', DEFAULT_SHOW_SKIN);

$query = sprintf(QUERY, $showSource ? SOURCE_PART : '', $count);
$title = sprintf(ngettext(
  'A randomly chosen word',
  '%d randomly chosen words',
  $count), $count);
$forms = DB::getArrayOfRows($query);

Smart::assign([
  'forms' => $forms,
  'title' => $title,
]);
if ($skin) {
  Smart::display('aggregate/randomWords.tpl');
} else {
  Smart::displayWithoutSkin('bits/randomWordsSimple.tpl');
}

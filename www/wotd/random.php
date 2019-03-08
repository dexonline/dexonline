<?php

const MIN_COUNT = 5;
const MAX_COUNT = 50;
const DEFAULT_COUNT = 20;

const DEFAULT_SHOW_SKIN = true;

const QUERY =
  'select lexicon cuv ' .
  'from WordOfTheDay W ' .
  'join Definition D on W.definitionId = D.id ' .
  'order by rand() ' .
  'limit %d';

$count = Request::getInRange('count', DEFAULT_COUNT, MIN_COUNT, MAX_COUNT);

$skin = Request::get('skin', DEFAULT_SHOW_SKIN);

$query = sprintf(QUERY, $count);
$title = sprintf(ngettext(
  'A randomly chosen word of the day',
  '%d randomly chosen words of the day',
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

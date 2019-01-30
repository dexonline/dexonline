<?php

require_once '../../phplib/Core.php';
User::mustHave(User::PRIV_WOTD);
Util::assertNotMirror();

$oper = Request::get('oper');
$id = Request::get('id');
$displayDate = Request::get('displayDate');
$noYear = Request::get('noYear');
$priority = Request::get('priority');
$definitionId = Request::get('definitionId');
$image = Request::getRaw('image'); // do not convert e.g. ş to ș
$description = Request::get('description');

switch ($oper) {
  case 'add':
    print saveWotd(null, $definitionId, $displayDate, $noYear, $priority, $image, $description);
    break;
  case 'edit':
    print saveWotd($id, $definitionId, $displayDate, $noYear, $priority, $image, $description);
    break;
  case 'del':
    print deleteWotd($id);
    break;
}

/*************************************************************************/

function deleteWotd($id) {
  $wotd = WordOfTheDay::get_by_id($id);
  if ($wotd) {
    $wotd->delete();
    return '';
  } else {
    return "Înregistrarea de șters nu a fost găsită (id={$id})";
  }
}

function saveWotd($id, $definitionId, $displayDate, $noYear, $priority, $image, $description) {
  // load / create object
  if ($id) {
    $wotd = WordOfTheDay::get_by_id($id);
  } else {
    $wotd = Model::factory('WordOfTheDay')->create();
    $wotd->userId = User::getActiveId();
  }

  // also compensate for some behavior in bootstrap-datepicker which submits
  // 0-MM-DD instead of 0000-MM-DD.
  if (($noYear && $displayDate)
      || Str::startsWith($displayDate, '0-')) {
    $displayDate = preg_replace('/^\d+/', '0000', $displayDate);
  }

  // validation
  $today = date('Y-m-d');

  $setInPast = $displayDate && !$noYear && $displayDate < $today;
  if ($setInPast && ($displayDate != $wotd->displayDate)) {
    return 'Nu puteți atribui o dată din trecut.';
  }

  $isPast = $wotd->hasFullDate() && $wotd->displayDate < $today;
  if ($isPast && $displayDate != $wotd->displayDate) {
    return 'Nu puteți modifica data pentru un cuvânt al zilei deja afișat.';
  }

  // We allow WotDs with no definition if the reason and date are set. A use
  // case is: we notice that event X happens on date D and we want to
  // celebrate it, but we don't have the time to find a word right now.
  if (!$definitionId &&
      (!$description || !$displayDate)) {
    return 'Dacă nu alegeți o definiție, atunci trebuie să alegeți o dată și un motiv.';
  }

  // save the WotD
  $wotd->displayDate = $displayDate ?: '0000-00-00';
  $wotd->definitionId = $definitionId;
  $wotd->priority = $priority;
  $wotd->image = $image;
  $wotd->description = $description;
  $wotd->save();

  return '';
}

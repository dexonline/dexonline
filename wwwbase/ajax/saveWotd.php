<?php

require_once('../../phplib/Core.php');
User::mustHave(User::PRIV_WOTD);
Util::assertNotMirror();

$oper = Request::get('oper');
$id = Request::get('id');
$displayDate = Request::get('displayDate');
$priority = Request::get('priority');
$definitionId = Request::get('definitionId');
$image = Request::getRaw('image'); // do not convert e.g. ş to ș
$description = Request::get('description');

switch ($oper) {
  case 'add':
    print saveWotd(null, $definitionId, $displayDate, $priority, $image, $description);
    break;
  case 'edit':
    print saveWotd($id, $definitionId, $displayDate, $priority, $image, $description);
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

function saveWotd($id, $definitionId, $displayDate, $priority, $image, $description) {
  // load / create object
  if ($id) {
    $wotd = WordOfTheDay::get_by_id($id);
  } else {
    $wotd = Model::factory('WordOfTheDay')->create();
    $wotd->userId = User::getActiveId();
  }

  // validation
  $today = date('Y-m-d', time());

  $setInPast = $displayDate && $displayDate < $today;
  if ($setInPast && ($displayDate != $wotd->displayDate)) {
    return 'Nu puteți atribui o dată din trecut.';
  }

  $isPast = $wotd->displayDate && $wotd->displayDate < $today;
  if ($isPast && $displayDate != $wotd->displayDate) {
    return 'Nu puteți modifica data pentru un cuvânt al zilei deja afișat.';
  }

  if (!$definitionId) {
    return 'Trebuie să alegeți o definiție';
  }

  // save the WotD
  $wotd->displayDate = $displayDate ?: null;
  $wotd->priority = $priority;
  $wotd->image = $image;
  $wotd->description = $description;
  $wotd->save();

  // when creating a WotD, also create a WotdRel object
  if (!$id) {
    $wotdr = Model::factory('WordOfTheDayRel')->create();
    $wotdr->wotdId = $wotd->id;
    $wotdr->refId = $definitionId;
    $wotdr->save();
  }

  return '';
}

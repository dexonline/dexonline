<?php
require_once('../phplib/util.php');
util_assertModerator(PRIV_WOTD);

$id = util_getRequestParameter('id');
$submitButton = util_getRequestParameter('submitButton');
$artist = $id
        ? WotdArtist::get_by_id($id)
        : Model::factory('WotdArtist')->create();

if ($submitButton) {
  $artist->name = util_getRequestParameter('name');
  $artist->email = util_getRequestParameter('email');
  $artist->label = util_getRequestParameter('label');
  $artist->credits = util_getRequestParameter('credits');

  if (validate($artist)) {
    $artist->save();
    FlashMessage::add('Modificările au fost salvate', 'info');
    util_redirect('autori-imagini.php');
  }
}

SmartyWrap::assign('artist', $artist);
SmartyWrap::display('editare-autor-imagini.tpl');

/**
 * Returns true on success, false on errors.
 */
function validate($artist) {
  $success = true;
  if (!$artist->name) {
    FlashMessage::add('Numele nu poate fi vid.');
  }
  if (!$artist->label) {
    FlashMessage::add('Codul nu poate fi vid (îl folosim încă la cuvântul lunii).');
  }
  if (!$artist->credits) {
    FlashMessage::add('Creditele nu pot fi vide.');
  }

  return !FlashMessage::hasMessage();
}

?>

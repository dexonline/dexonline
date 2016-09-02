<?php
require_once('../phplib/util.php');
util_assertModerator(PRIV_WOTD);

$id = util_getRequestParameter('id');
$deleteId = util_getRequestParameter('deleteId');
$saveButton = util_getBoolean('saveButton');
$artist = $id
        ? WotdArtist::get_by_id($id)
        : Model::factory('WotdArtist')->create();

if ($deleteId) {
  WotdArtist::delete_all_by_id($deleteId);
  Log::info("Deleted author {$deleteId}");
  FlashMessage::add('Am șters autorul.', 'success');
  util_redirect('autori-imagini.php');
}

if ($saveButton) {
  $artist->name = util_getRequestParameter('name');
  $artist->email = util_getRequestParameter('email');
  $artist->label = util_getRequestParameter('label');
  $artist->credits = util_getRequestParameter('credits');

  if (validate($artist)) {
    $artist->save();
    Log::info("Added/saved author {$artist->id} ({$artist->name})");
    FlashMessage::add('Am salvat modificările.', 'success');
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

  $other = Model::factory('WotdArtist')
         ->where('label', $artist->label)
         ->where_not_equal('id', (int) $artist->id) // could be "" when adding a new artist
         ->find_one();
  if ($other) {
    FlashMessage::add('Codul este deja folosit.');
  }

  return !FlashMessage::hasErrors();
}

?>

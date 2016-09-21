<?php
require_once('../phplib/util.php');
util_assertModerator(PRIV_ADMIN);

$sourceId = Request::get('id');
$saveButton = Request::isset('saveButton');
$src = $sourceId ? Source::get_by_id($sourceId) : Model::factory('Source')->create();

if ($saveButton) {
  $src->name = Request::get('name');
  $src->shortName = Request::get('shortName');
  $src->urlName = Request::get('urlName');
  $src->author = Request::get('author');
  $src->publisher = Request::get('publisher');
  $src->year = Request::get('year');
  $src->link = Request::get('link');
  $src->isActive = Request::isset('isActive');
  $src->type = Request::get('type');
  $src->canContribute = Request::isset('canContribute');
  $src->canModerate = Request::isset('canModerate');
  $src->canDistribute = Request::isset('canDistribute');
  $src->defCount = Request::get('defCount');

  if (validate($src)) {
    // For new sources, set displayOrder to the highest available + 1
    if (!$sourceId) {
      $src->displayOrder = Model::factory('Source')->count() + 1;
    }
    $src->updatePercentComplete();
    $src->save();
    Log::notice("Added/saved source {$src->id} ({$src->shortName})");
    FlashMessage::add('Am salvat modificările.', 'success');
    util_redirect("editare-sursa?id={$src->id}");
  }
}

SmartyWrap::assign('src', $src);
SmartyWrap::display('editare-sursa.tpl');

/**
 * Returns true on success, false on errors.
 */
function validate($src) {
  if (!$src->name) {
    FlashMessage::add('Numele nu poate fi vid.');
  }
  if (!$src->shortName) {
    FlashMessage::add('Numele scurt nu poate fi vid.');
  }
  if (!$src->urlName) {
    FlashMessage::add('Numele URL nu poate fi vid.');
  }
  if (!$src->author) {
    FlashMessage::add('Autorul nu poate fi vid.');
  }
  if ($src->defCount < 0 && $src->defCount != Source::$UNKNOWN_DEF_COUNT) {
    FlashMessage::add('Numărul de definiții trebuie să fie pozitiv.');
  }
  return !FlashMessage::hasErrors();
}

?>

<?php
require_once('../phplib/util.php');
util_assertModerator(PRIV_ADMIN);

$sourceId = util_getRequestParameter('id');
$saveButton = util_getBoolean('saveButton');
$src = $sourceId ? Source::get_by_id($sourceId) : Model::factory('Source')->create();

if ($saveButton) {
  $src->name = util_getRequestParameter('name');
  $src->shortName = util_getRequestParameter('shortName');
  $src->urlName = util_getRequestParameter('urlName');
  $src->author = util_getRequestParameter('author');
  $src->publisher = util_getRequestParameter('publisher');
  $src->year = util_getRequestParameter('year');
  $src->link = util_getRequestParameter('link');
  $src->isActive = util_getBoolean('isActive');
  $src->type = util_getRequestParameter('type');
  $src->canContribute = util_getBoolean('canContribute');
  $src->canModerate = util_getBoolean('canModerate');
  $src->canDistribute = util_getBoolean('canDistribute');
  $src->defCount = util_getRequestIntParameter('defCount');

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

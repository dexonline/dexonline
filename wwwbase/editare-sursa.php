<?php
require_once('../phplib/util.php');
util_assertModerator(PRIV_ADMIN);

$sourceId = Request::get('id');
$saveButton = Request::has('saveButton');
$src = $sourceId ? Source::get_by_id($sourceId) : Model::factory('Source')->create();

if ($saveButton) {
  $src->name = Request::get('name');
  $src->shortName = Request::get('shortName');
  $src->urlName = Request::get('urlName');
  $src->author = Request::get('author');
  $src->publisher = Request::get('publisher');
  $src->year = Request::get('year');
  $src->sourceTypeId = Request::get('sourceTypeId');
  $src->managerId = Request::get('managerId');
  $src->importType = Request::get('importType');
  $src->reformId = Request::get('reformId');
  $src->remark = Request::get('remark');
  $src->link = Request::get('link');
  $src->courtesyLink = Request::get('courtesyLink');
  $src->courtesyText = Request::get('courtesyText');
  $src->isActive = Request::has('isActive');
  $src->type = Request::get('type');
  $src->canContribute = Request::has('canContribute');
  $src->canModerate = Request::has('canModerate');
  $src->canDistribute = Request::has('canDistribute');
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
SmartyWrap::assign("managers", Model::factory('User')->where_raw('moderator & 4')->order_by_asc('id')->find_many());
SmartyWrap::assign("sourceTypes", Model::factory('SourceType')->order_by_asc('id')->find_many());
SmartyWrap::assign("reforms", Model::factory('OrthographicReforms')->order_by_asc('id')->find_many());
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

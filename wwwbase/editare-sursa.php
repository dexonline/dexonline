<?php
require_once("../phplib/util.php");
util_assertModerator(PRIV_ADMIN);

$sourceId = util_getRequestParameter('id');
$submitButton = util_getRequestParameter('submitButton');
$src = $sourceId ? Source::get_by_id($sourceId) : Model::factory('Source')->create();

if ($submitButton) {
  $src->name = util_getRequestParameter("name");
  $src->shortName = util_getRequestParameter("shortName");
  $src->urlName = util_getRequestParameter("urlName");
  $src->author = util_getRequestParameter("author");
  $src->publisher = util_getRequestParameter("publisher");
  $src->year = util_getRequestParameter("year");
  $src->isOfficial = util_getRequestParameterWithDefault("isOfficial", 0);
  $src->canContribute = util_getRequestParameterWithDefault("canContribute", 0);
  $src->canModerate = util_getRequestParameterWithDefault("canModerate", 0);
  $src->canDistribute = util_getRequestParameterWithDefault("canDistribute", 0);
  $src->defCount = util_getRequestIntParameter("defCount");

  if (validate($src)) {
    // For new sources, set displayOrder to the highest available + 1
    if (!$sourceId) {
      $src->displayOrder = Model::factory('Source')->count() + 1;
    }
    $src->updatePercentComplete();
    $src->save();
    FlashMessage::add('Modificările au fost salvate', 'info');
    util_redirect("editare-sursa?id={$src->id}");
  }
}

SmartyWrap::assign('src', $src);
SmartyWrap::assign('page_title', $sourceId ? "Editare sursă {$src->shortName}" : "Adăugare sursă");
SmartyWrap::displayCommonPageWithSkin('editare-sursa.ihtml');

/**
 * Returns true on success, false on errors.
 */
function validate($src) {
  $success = true;
  if (!$src->name) { FlashMessage::add('Numele nu poate fi vid.'); $success = false; }
  if (!$src->shortName) { FlashMessage::add('Numele scurt nu poate fi vid.'); $success = false; }
  if (!$src->urlName) { FlashMessage::add('Numele URL nu poate fi vid.'); $success = false; }
  if (!$src->author) { FlashMessage::add('Autorul nu poate fi vid.'); $success = false; }

  if ($src->defCount < 0 && $src->defCount != Source::$UNKNOWN_DEF_COUNT) {
    FlashMessage::add("Numărul de definiții trebuie să fie pozitiv.");
    $success = false;
  }
  return $success;
}

?>

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
  $src->link = util_getRequestParameter("link");
  $src->isActive = util_getRequestParameterWithDefault("isActive", 0);
  $src->isOfficial = util_getRequestParameterWithDefault("isOfficial", 0);
  $src->canContribute = util_getRequestParameterWithDefault("canContribute", 0);
  $src->canModerate = util_getRequestParameterWithDefault("canModerate", 0);
  $src->canDistribute = util_getRequestParameterWithDefault("canDistribute", 0);
  $src->defCount = util_getRequestIntParameter("defCount");

  $existing_curators = Curator::getCuratorsForSource($sourceId);
  $curators = array_unique(util_getRequestIntArrayParameter('curators'));

  if (validate($src)) {
    // For new sources, set displayOrder to the highest available + 1
    if (!$sourceId) {
      $src->displayOrder = Model::factory('Source')->count() + 1;
    }
    $src->updatePercentComplete();
    try {
      ORM::get_db()->beginTransaction();
      $src->save();
      // Delete curators no longer assigned
      $existing_ids = array();
      /** @var User $user */
      foreach($existing_curators as $user) {
        $existing_ids[] = $user->id();
        if(!in_array($user->id(), $curators)) {
          Model::factory('Curator')->where(array('sourceId' => $sourceId, 'userId' => $user->id()))->delete_many();
        }
      }
      // Attach new curators
      foreach($curators as $userId) {
        if(!in_array($userId, $existing_ids)) {
          $curator = Model::factory('Curator')->create();
          $curator->sourceId = $sourceId;
          $curator->userId = $userId;
          $curator->save();
        }
      }
      ORM::get_db()->commit();
      FlashMessage::add('Modificările au fost salvate', 'info');
    }
    catch(Exception $e) {
      ORM::get_db()->rollBack();
      FlashMessage::add('Modificările au putut fi salvate, err:' . $e->getMessage());
    }
    util_redirect("editare-sursa?id={$src->id}");
  }
}

SmartyWrap::assign('curators', Curator::getCuratorsForSource($sourceId));
SmartyWrap::assign('src', $src);
SmartyWrap::assign('page_title', $sourceId ? "Editare sursă {$src->shortName}" : "Adăugare sursă");
SmartyWrap::addCss('select2');
SmartyWrap::addJs('jqueryui', 'select2', 'editareSursa');
SmartyWrap::display('editare-sursa.ihtml');

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

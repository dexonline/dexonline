<?php
require_once("../phplib/util.php");

$value = util_getRequestParameter('value');
$saveButton = util_getRequestParameter('saveButton');
$jsonTags = util_getRequestParameter('jsonTags');

if ($saveButton) {
  util_assertModerator(PRIV_ADMIN);

  // Build a map of all Tag IDs so we can delete those that are gone.
  $ids = Model::factory('Tag')
       ->select('id')
       ->find_array();

  $idMap = [];
  foreach($ids as $rec) {
    $idMap[$rec['id']] = 1;
  }

  // For each level, store (1) the last tag ID seen and (2) the current
  // number of children
  $tagIds = [ 0 ];
  $numChildren = [ 0 ];

  foreach (json_decode($jsonTags) as $rec) {
    if ($rec->id) {
      $t = Tag::get_by_id($rec->id);
      unset($idMap[$rec->id]);
    } else {
      $t = Model::factory('Tag')->create();
    }
    $t->value = $rec->value;
    $t->parentId = $tagIds[$rec->level - 1];
    $t->displayOrder = ++$numChildren[$rec->level - 1];
    $t->save();
    $tagIds[$rec->level] = $t->id;
    $numChildren[$rec->level] = 0;
  }

  foreach ($idMap as $id => $ignored) {
    Tag::delete_all_by_id($id);
  }
  Log::notice('Saved tag tree');
  FlashMessage::add('Am salvat etichetele.', 'success');
  util_redirect('etichete');
}

SmartyWrap::assign('tags', Tag::loadTree());
SmartyWrap::assign('suggestNoBanner', true);
SmartyWrap::assign('suggestHiddenSearchForm', true);
SmartyWrap::display('etichete.tpl');

?>

<?php
require_once("../phplib/util.php");

$value = util_getRequestParameter('value');
$saveButton = util_getRequestParameter('saveButton');
$jsonTags = util_getRequestParameter('jsonTags');

if ($saveButton) {
  util_assertModerator(PRIV_ADMIN);

  // Build a map of all MeaningTag IDs so we can delete those that are gone.
  $ids = Model::factory('MeaningTag')
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
      $mt = MeaningTag::get_by_id($rec->id);
      unset($idMap[$rec->id]);
    } else {
      $mt = Model::factory('MeaningTag')->create();
    }
    $mt->value = $rec->value;
    $mt->parentId = $tagIds[$rec->level - 1];
    $mt->displayOrder = ++$numChildren[$rec->level - 1];
    $mt->save();
    $tagIds[$rec->level] = $mt->id;
    $numChildren[$rec->level] = 0;
  }

  foreach ($idMap as $id => $ignored) {
    MeaningTag::delete_all_by_id($id);
  }
  FlashMessage::add('Am salvat etichetele.', 'info');
  util_redirect('etichete-sensuri');
}

SmartyWrap::assign('meaningTags', MeaningTag::loadTree());
SmartyWrap::assign('suggestNoBanner', true);
SmartyWrap::assign('suggestHiddenSearchForm', true);
SmartyWrap::addCss('etichete-sensuri');
SmartyWrap::addjs('etichete-sensuri');
SmartyWrap::display('etichete-sensuri.tpl');

?>

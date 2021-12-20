<?php
User::mustHave(User::PRIV_VISUAL);

function deleteTag() {
  $tagId = Request::get('tagId');
  $vt = VisualTag::get_by_id($tagId);
  if ($vt) {
    Log::notice("Deleted visual tag {$vt->id} ({$vt->label}) for image {$vt->imageId}");
    $vt->delete();
  }

  // return the remaining tag info for the visual
  $v = Visual::get_by_id($vt->imageId);
  header('Content-Type: application/json');
  echo $v->getTagInfo();
}

function loadTags() {
  $visualId = Request::get('visualId');

  $visual = Visual::get_by_id($visualId);
  if (!$visual) {
    FlashMessage::add('Imaginea nu există.');
    Util::redirectToHome();
  }

  // extended with the entry's description
  $tags = Model::factory('VisualTag')
    ->table_alias('vt')
    ->select('vt.*')
    ->select('e.description')
    ->join('Entry', [ 'vt.entryId', '=', 'e.id' ], 'e')
    ->where('vt.imageId', $visualId)
    ->order_by_asc('e.description')
    ->find_array();

  header('Content-Type: application/json');
  echo json_encode($tags);
}

function saveField() {
  $tagId = Request::get('tagId');
  $field = Request::get('field');
  $value = Request::get('value');

  $vt = VisualTag::get_by_id($tagId);
  if ($vt) {
    $vt->$field = $value;
    $vt->save();
    Log::notice("Updated visual tag {$vt->id} ({$vt->label}), set {$field}={$value}");
  }

  // return the remaining tag info for the visual
  $v = Visual::get_by_id($vt->imageId);
  header('Content-Type: application/json');
  echo $v->getTagInfo();
}

function main() {
  $action = Request::get('action');

  switch ($action) {
    case 'delete': deleteTag(); break;
    case 'load': loadTags(); break;
    case 'save': saveField(); break;
  }
}

main();

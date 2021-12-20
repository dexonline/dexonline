<?php
User::mustHave(User::PRIV_VISUAL);

$fileName = Request::get('fileName');
$id = Request::get('id');
$entryId = Request::get('entryId');
$revised = Request::has('revised');
$saveButton = Request::has('saveButton');
$tagEntryId = Request::get('tagEntryId');
$tagLabel = Request::get('tagLabel');
$labelX = Request::get('labelX');
$labelY = Request::get('labelY');
$tipX = Request::get('tipX');
$tipY = Request::get('tipY');
$addTagButton = Request::has('addTagButton');
$userId = User::getActiveId();

// Tag the image specified by $fileName. Create a Visual object if one doesn't
// exist, then redirect to it.
if ($fileName) {
  $v = Visual::get_by_path($fileName);
  if (!$v) {
    $v = Visual::createFromFile($fileName);
  }
  Util::redirect("?id={$v->id}");
}

$v = Visual::get_by_id($id);

if ($saveButton) {
  $v->entryId = $entryId;
  $v->revised = $revised;
  $v->save();
  Log::notice("Saved image {$v->id} ({$v->path})");
  Util::redirect("?id={$v->id}");
}

if ($addTagButton) {
  $vt = Model::factory('VisualTag')->create();
  $vt->imageId = $v->id;
  $vt->entryId = $tagEntryId;
  $vt->label = $tagLabel;
  $vt->labelX = $labelX;
  $vt->labelY = $labelY;
  $vt->tipX = $tipX;
  $vt->tipY = $tipY;
  $vt->userId = $userId;
  $vt->save();
  Log::info("Added tag {$vt->id} ({$vt->label}) to image {$v->id} ({$v->path})");
  Util::redirect("?id={$v->id}");
}

Smart::assign('visual', $v);
Smart::addResources('gallery', 'jcrop', 'select2Dev', 'tabulator');
Smart::display('visual/tagger.tpl');

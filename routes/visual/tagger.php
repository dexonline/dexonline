<?php
User::mustHave(User::PRIV_VISUAL);

$fileName = Request::get('fileName');
$id = Request::get('id');
$entryId = Request::get('entryId');
$revised = Request::has('revised');
$saveButton = Request::has('saveButton');
$tagEntryId = Request::get('tagEntryId');
$tagLabel = Request::get('tagLabel');
$textXCoord = Request::get('textXCoord');
$textYCoord = Request::get('textYCoord');
$imgXCoord = Request::get('imgXCoord');
$imgYCoord = Request::get('imgYCoord');
$addTagButton = Request::has('addTagButton');
$userId = User::getActiveId();

// Tag the image specified by $fileName. Create a Visual object if one doesn't exist, then redirect to it.
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
  $vt->textXCoord = $textXCoord;
  $vt->textYCoord = $textYCoord;
  $vt->imgXCoord = $imgXCoord;
  $vt->imgYCoord = $imgYCoord;
  $vt->userId = $userId;
  $vt->save();
  Log::info("Added tag {$vt->id} ({$vt->label}) to image {$v->id} ({$v->path})");
  Util::redirect("?id={$v->id}");
}

Smart::assign('visual', $v);
Smart::assign('entry', Entry::get_by_id($v->entryId));

Smart::addResources('jcrop', 'jqgrid', 'gallery', 'admin', 'select2Dev');
Smart::display('visual/tagger.tpl');

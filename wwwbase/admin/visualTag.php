<?php
require_once('../../phplib/Core.php');
User::mustHave(User::PRIV_VISUAL);
Util::assertNotMirror();

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
  $vt->save();
  Log::info("Added tag {$vt->id} ({$vt->label}) to image {$v->id} ({$v->path})");
  Util::redirect("?id={$v->id}");
}

SmartyWrap::assign('visual', $v);
SmartyWrap::assign('entry', Entry::get_by_id($v->entryId));

SmartyWrap::addCss('jqueryui', 'jcrop', 'jqgrid', 'gallery', 'admin');
SmartyWrap::addJs('jqueryui', 'jcrop', 'select2Dev', 'jqgrid', 'gallery', 'jcanvas'); 
SmartyWrap::display('admin/visualTag.tpl');

?>

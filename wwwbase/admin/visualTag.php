<?php
require_once('../../phplib/util.php');
util_assertModerator(PRIV_VISUAL);
util_assertNotMirror();

$fileName = util_getRequestParameter('fileName');
$id = util_getRequestParameter('id');
$entryId = util_getRequestParameter('entryId');
$revised = util_getBoolean('revised');
$saveButton = util_getBoolean('saveButton');
$tagEntryId = util_getRequestParameter('tagEntryId');
$tagLabel = util_getRequestParameter('tagLabel');
$textXCoord = util_getRequestParameter('textXCoord');
$textYCoord = util_getRequestParameter('textYCoord');
$imgXCoord = util_getRequestParameter('imgXCoord');
$imgYCoord = util_getRequestParameter('imgYCoord');
$addTagButton = util_getBoolean('addTagButton');

// Tag the image specified by $fileName. Create a Visual object if one doesn't exist, then redirect to it.
if ($fileName) {
  $v = Visual::get_by_path($fileName);
  if (!$v) {
    $v = Visual::createFromFile($fileName);
  }
  util_redirect("?id={$v->id}");
}

$v = Visual::get_by_id($id);

if ($saveButton) {
  $v->entryId = $entryId;
  $v->revised = $revised;
  $v->save();
  Log::notice("Saved image {$v->id} ({$v->path})");
  util_redirect("?id={$v->id}");
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
  util_redirect("?id={$v->id}");
}

SmartyWrap::assign('visual', $v);
SmartyWrap::assign('entry', Entry::get_by_id($v->entryId));

SmartyWrap::addCss('jqueryui-smoothness', 'jcrop', 'select2', 'jqgrid', 'jqueryui', 'gallery',
                   'admin');
SmartyWrap::addJs('jqueryui', 'jcrop', 'select2', 'jqgrid', 'gallery', 'jcanvas'); 
SmartyWrap::display('admin/visualTag.tpl');

?>

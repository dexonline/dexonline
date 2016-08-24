<?php
require_once('../../phplib/util.php');
util_assertModerator(PRIV_VISUAL);
util_assertNotMirror();
RecentLink::createOrUpdate('Etichetare Imagini Definiții');

$fileName = util_getRequestParameter('fileName');
$id = util_getRequestParameter('id');
$entryId = util_getRequestParameter('entryId');
$revised = util_getBoolean('revised');
$saveButton = util_getRequestParameter('saveButton');
$tagEntryId = util_getRequestParameter('tagEntryId');
$tagLabel = util_getRequestParameter('tagLabel');
$textXCoord = util_getRequestParameter('textXCoord');
$textYCoord = util_getRequestParameter('textYCoord');
$imgXCoord = util_getRequestParameter('imgXCoord');
$imgYCoord = util_getRequestParameter('imgYCoord');
$addTagButton = util_getRequestParameter('addTagButton');

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

SmartyWrap::addCss('jqueryui-smoothness', 'jcrop', 'select2', 'jqgrid', 'jqueryui', 'gallery');
SmartyWrap::addJs('jqueryui', 'jcrop', 'select2', 'select2Dev', 'jqgrid', 'gallery', 'jcanvas'); 
SmartyWrap::displayAdminPage('admin/visualTag.tpl');

?>

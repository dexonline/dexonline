<?php
require_once('../../phplib/util.php');
util_assertModerator(PRIV_VISUAL);
util_assertNotMirror();
RecentLink::createOrUpdate('Etichetare Imagini Definiții');

$fileName = util_getRequestParameter('fileName');
$id = util_getRequestParameter('id');
$lexemId = util_getRequestParameter('lexemId');
$revised = util_getBoolean('revised');
$saveButton = util_getRequestParameter('saveButton');
$tagLexemId = util_getRequestParameter('tagLexemId');
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
  $v->lexemeId = $lexemId;
  $v->revised = $revised;
  $v->save();
  util_redirect("?id={$v->id}");
}

if ($addTagButton) {
  $vt = Model::factory('VisualTag')->create();
  $vt->imageId = $v->id;
  $vt->lexemeId = $tagLexemId;
  $vt->label = $tagLabel;
  $vt->textXCoord = $textXCoord;
  $vt->textYCoord = $textYCoord;
  $vt->imgXCoord = $imgXCoord;
  $vt->imgYCoord = $imgYCoord;
  $vt->save();
  util_redirect("?id={$v->id}");
}

SmartyWrap::assign('visual', $v);
SmartyWrap::assign('lexem', Lexem::get_by_id($v->lexemeId));

SmartyWrap::assign('sectionTitle', 'Etichetare imagini pentru definiții');
SmartyWrap::addCss('jqueryui-smoothness', 'jcrop', 'select2', 'jqgrid', 'jqueryui', 'gallery');
SmartyWrap::addJs('jquery', 'jqueryui', 'jcrop', 'visualTag', 'select2', 'select2Dev', 'jqgrid', 'gallery'); 
SmartyWrap::displayAdminPage('admin/visualTag.tpl');

?>

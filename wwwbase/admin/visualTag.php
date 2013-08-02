<?php
require_once '../../phplib/util.php' ;
require_once '../../phplib/models/Visual.php' ;
//util_assertModerator(PRIV_VISUAL);
util_assertNotMirror();
RecentLink::createOrUpdate('Tăguire Imagini Definiții');

$rootPath = util_getImgRoot() . '/';
$savedTags = '';

if(util_getRequestParameter('action') == 'save') {
  $imageId = util_getRequestParameter('imageId');
  $lexem = util_getRequestParameter('lexem');
  $xTag = util_getRequestParameter('xTag');
  $yTag = util_getRequestParameter('yTag');
  $xImg = util_getRequestParameter('xImg');
  $yImg = util_getRequestParameter('yImg');
  $isMain = util_getRequestParameter('isMain');

  $line = Model::factory('VisualTag')->create();
  $line->imageId = $imageId;
  $line->isMain = $isMain;
  $line->label = $lexem;
  $line->textXCoord = $xTag;
  $line->textYCoord = $yTag;
  $line->imgXCoord = $xImg;
  $line->imgYCoord = $yImg;
  $line->save();

} else if(util_getRequestParameter('action') == 'delete') {
  $tagId = util_getRequestParameter('savedTagId');

  $line = VisualTag::get_by_id($tagId);
  if(!empty($line)) {
    $line->delete();
  }

} else if(util_getRequestParameter('action') == 'finishedTagging') {
  $imageId = util_getRequestParameter('imageId');

  $line = Visual::get_by_id($imageId);
  $line->revised = 1;
  $line->save();
}

//$line = Model::factory('Visual')->where('revised', 0)->find_one();
$line = Visual::get_by_revised(0);
SmartyWrap::assign('anyUntaggedImages', !empty($line));
if(!empty($line)) {
  $imagePath = $rootPath . $line->path;
  $imageId = $line->id;

  $tags = VisualTag::get_all_by_imageId($imageId);

  SmartyWrap::assign('savedTags', $tags);
  SmartyWrap::assign('imagePath', $imagePath);
  SmartyWrap::assign('imageId', $imageId);
}

SmartyWrap::assign('sectionTitle', 'Etichetare imagini pentru definiții');
SmartyWrap::addCss('jcrop');
SmartyWrap::addJs('jquery', 'jcrop', 'visualTag');
SmartyWrap::displayAdminPage('admin/visualTag.ihtml'); 

<?php
require_once '../../phplib/util.php' ;
require_once '../../phplib/models/Visual.php' ;
util_assertModerator(PRIV_VISUAL);
util_assertNotMirror();
RecentLink::createOrUpdate('Etichetare Imagini Definiții');

$rootPath = util_getImgRoot() . '/';
$savedTags = '';
$action = util_getRequestParameter('action');


if($action == 'save') {
  $imageId = util_getRequestParameter('imageId');
  $lexemeId = util_getrequestParameter('lexemeId');
  $label = util_getRequestParameter('label');
  $xTag = util_getRequestParameter('xTag');
  $yTag = util_getRequestParameter('yTag');
  $xImg = util_getRequestParameter('xImg');
  $yImg = util_getRequestParameter('yImg');

  $line = Model::factory('VisualTag')->create();
  $line->imageId = $imageId;
  $line->lexemeId = $lexemeId;
  $line->label = $label;
  $line->textXCoord = $xTag;
  $line->textYCoord = $yTag;
  $line->imgXCoord = $xImg;
  $line->imgYCoord = $yImg;
  $line->save();

  util_redirect(util_getWwwRoot() . 'admin/visualTag.php');

} else if($action == 'delete') {
  $tagId = util_getRequestParameter('savedTagId');

  $line = VisualTag::get_by_id($tagId);
  if(!empty($line)) {
    $line->delete();
  }

  util_redirect(util_getWwwRoot() . 'admin/visualTag.php');

} else if($action == 'finishedTagging') {
  $imageId = util_getRequestParameter('imageId');

  $line = Visual::get_by_id($imageId);
  
  if(!empty($line)) {
    $line->revised = 1;
    $line->save();
  }

  util_redirect(util_getWwwRoot() . 'admin/visualTag.php');

} else if($action == 'setImgLexemeId') {
  $imgLexemeId = util_getRequestParameter('imgLexemeId');
  $imageId = util_getRequestParameter('imageId');

  $line = Visual::get_by_id($imageId);
  
  if(!empty($line)){
    $line->lexemeId = $imgLexemeId;
    $line->save();
  }

  util_redirect(util_getWwwRoot() . 'admin/visualTag.php');

} else if($action == 'resetImgLexemeId') {
  $imageId = util_getRequestParameter('imageId');

  $line = Visual::get_by_id($imageId);

  if(!empty($line)) {
    $line->lexemeId = '';
    $line->save();
  }

  util_redirect(util_getWwwRoot() . 'admin/visualTag.php');
}

$line = Visual::get_by_revised(0);
SmartyWrap::assign('anyUntaggedImages', !empty($line));
if(!empty($line)) {
  $imagePath = $rootPath . $line->path;
  $imageId = $line->id;
  $imgLexemeId = $line->lexemeId;
  if($imgLexemeId) {
    $lexemeName = Lexem::get_by_id($imgLexemeId);
    $lexemeName = $lexemeName->formUtf8General;
    SmartyWrap::assign('lexemeName', $lexemeName);
  }

  $tags = VisualTag::get_all_by_imageId($imageId);

  SmartyWrap::assign('savedTags', $tags);
  SmartyWrap::assign('imagePath', $imagePath);
  SmartyWrap::assign('imageId', $imageId);
  SmartyWrap::assign('imgLexemeId', $imgLexemeId);
}

SmartyWrap::assign('sectionTitle', 'Etichetare imagini pentru definiții');
SmartyWrap::addCss('jcrop', 'select2', 'jqgrid', 'jqueryui');
SmartyWrap::addJs('jquery', 'jcrop', 'visualTag', 'select2', 'select2Dev', 'jqgrid');
SmartyWrap::displayAdminPage('admin/visualTag.ihtml'); 

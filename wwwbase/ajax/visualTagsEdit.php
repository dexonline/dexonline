<?php
require_once("../../phplib/util.php");

$oper = util_getRequestParameter('oper');
$id = util_getRequestParameter('id');

switch ($oper) {
  case 'del':
    $line = VisualTag::get_by_id($id);
    
    if(!empty($line)){
      $line->delete();
    }   
    break;
  
  case 'edit':
    $xTag = util_getRequestParameter('xTag');
    $yTag = util_getRequestParameter('yTag');
    $xImg = util_getRequestParameter('xImg');
    $yImg = util_getRequestParameter('yImg');
    $label = util_getRequestParameter('label');

    $line = VisualTag::get_by_id($id);
  	
    if(!empty($line)){
      $line->textXCoord = $xTag;
      $line->textYCoord = $yTag;
      $line->imgXCoord = $xImg;
      $line->imgYCoord = $yImg;
      $line->label = $label;
      $line->save();
  	}
    break;

  default:
    break;
}
?>

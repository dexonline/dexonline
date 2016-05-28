<?php
require_once("../../phplib/util.php");

$oper = util_getRequestParameter('oper');
$id = util_getRequestParameter('id');

switch ($oper) {
  case 'del':
    $line = VisualTag::get_by_id($id);
    
    if(!empty($line)){
      Log::notice("Deleted visual tag {$line->id} ({$line->label}) for image {$line->imageId}");
      $line->delete();
    }   
    break;
  
  case 'edit':
    $line = VisualTag::get_by_id($id);
  	
    if(!empty($line)){
      $line->textXCoord = util_getRequestParameter('textXCoord');
      $line->textYCoord = util_getRequestParameter('textYCoord');;
      $line->imgXCoord = util_getRequestParameter('imgXCoord');
      $line->imgYCoord = util_getRequestParameter('imgYCoord');
      $line->label = util_getRequestParameter('label');
      $line->save();
      Log::notice("Edited visual tag {$line->id} ({$line->label}) for image {$line->imageId}");
  	}
    break;

  default:
    break;
}
?>

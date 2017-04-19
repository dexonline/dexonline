<?php
require_once("../../phplib/Core.php");

$oper = Request::get('oper');
$id = Request::get('id');

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
      $line->textXCoord = Request::get('textXCoord');
      $line->textYCoord = Request::get('textYCoord');;
      $line->imgXCoord = Request::get('imgXCoord');
      $line->imgYCoord = Request::get('imgYCoord');
      $line->label = Request::get('label');
      $line->save();
      Log::notice("Edited visual tag {$line->id} ({$line->label}) for image {$line->imageId}");
  	}
    break;

  default:
    break;
}
?>

<?php

require_once('../../phplib/util.php');

$imageId = util_getRequestParameter('imageId');
$page = util_getRequestParameter('page');
$limit = util_getRequestParameter('rows');
$usage = util_getRequestParameter('usage');
$resp = array();

if($usage == 'table') {
  $total = Model::factory('VisualTag')->where('imageId', $imageId)->count();
  $lines = Model::factory('VisualTag')->where('imageId', $imageId)
                                      ->limit($limit)->offset(($page - 1) * $limit)->find_many();

} else if($usage == 'gallery') {
  $image = Visual::get_by_id($imageId);
  $dims = array('width' => $image->width, 'height' => $image->height);
  $lines = VisualTag::get_all_by_imageId($imageId);
}

foreach($lines as $line) {
  $row = Lexem::get_by_id($line->lexemeId);
  $tags[] = array('id' => $line->id, 'label' => $line->label, 'textX' => $line->textXCoord,
                  'textY' => $line->textYCoord, 'imgX' => $line->imgXCoord,
                  'imgY' => $line->imgYCoord, 'lexeme' => !empty($row) ? $row->formUtf8General : '');
}

if($usage == 'table') {
  $resp = array('total' => ceil($total / $limit), 'page' => $page, 'records' => $total, 'rows' => $tags);

} else if($usage == 'gallery') {
  $resp = array('dims' => $dims, 'tags' => $tags);
}

echo json_encode($resp);
?>
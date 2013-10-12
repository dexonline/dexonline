<?php

require_once("../../phplib/util.php");

$imageId = util_getRequestParameter('imageId');
$page = util_getRequestParameter('page');
$limit = util_getRequestParameter('rows');
$rows = array();
$total = VisualTag::factory('VisualTag')->where('imageId', $imageId)->count();
$lines = VisualTag::factory('VisualTag')->where('imageId', $imageId)
										->filter('limit', ($page - 1) * $limit, $limit)->find_many();

foreach ($lines as $line) {
  $row = Lexem::get_by_id($line->lexemeId);
  $rows[] = array('id' => $line->id, 'label' => $line->label, 'xTag' => $line->textXCoord,
                  'yTag' => $line->textYCoord, 'xImg' => $line->imgXCoord,
                  'yImg' => $line->imgYCoord, 'lexeme' => !empty($row) ? $row->formUtf8General : '');
}

$resp = array('total' => ceil($total / $limit), 'page' => $page, 'records' => $total, 'rows' => $rows);

echo json_encode($resp);
?>
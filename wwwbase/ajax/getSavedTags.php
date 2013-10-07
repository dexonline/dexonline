<?php

require_once("../../phplib/util.php");

$imageId = util_getRequestParameter('imageId');
$resp = array();
$lines = VisualTag::get_all_by_imageId($imageId);

foreach ($lines as $line) {
  $row = Lexem::get_by_id($line->lexemeId);
  $resp[] = array('id' => $line->id, 'label' => $line->label, 'xTag' => $line->textXCoord,
                  'yTag' => $line->textYCoord, 'xImg' => $line->imgXCoord,
                  'yImg' => $line->imgYCoord, 'lexeme' => !empty($row) ? $row->formUtf8General : '');
}

echo json_encode($resp);
?>
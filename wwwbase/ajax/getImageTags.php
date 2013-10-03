<?php
include('../../phplib/util.php');

$resp = array();
$tags = array();
$dims = array();

$imageId = util_getRequestParameter('imageId');

$line = Visual::get_by_id($imageId);

if(!empty($line)) {
  $dims = array('width' => (int)$line->width, 'height' => (int)$line->height);
}

$lines = VisualTag::get_all_by_imageId($imageId);

foreach($lines as $line) {
  $row = Lexem::get_by_id($line->lexemeId);
  $tags[] = array('textX' => (int)$line->textXCoord, 'textY' => (int)$line->textYCoord,
                  'imgX' => (int)$line->imgXCoord, 'imgY' => (int)$line->imgYCoord,
                  'label' => $line->label, 'lexeme' => $row->formUtf8General);
}

$resp = array('dims' => $dims, 'tags' => $tags);

echo json_encode($resp);
?>

<?php

require_once('../../phplib/Core.php');

$visualId = Request::get('visualId');
$page = Request::get('page');
$limit = Request::get('rows');
$usage = Request::get('usage');
$resp = array(); $tags = array();

if ($usage == 'table') {
  $total = Model::factory('VisualTag')->where('imageId', $visualId)->count();
  $lines = Model::factory('VisualTag')->where('imageId', $visualId)
                                      ->limit($limit)->offset(($page - 1) * $limit)->find_many();

} else if ($usage == 'gallery') {
  $image = Visual::get_by_id($visualId);
  $dims = array('width' => $image->width, 'height' => $image->height);
  $lines = VisualTag::get_all_by_imageId($visualId);
}

foreach ($lines as $line) {
  // Link to one of the entry's lexems.
  // TODO: implement entry search and link to the entry itself.
  $entry = Entry::get_by_id($line->entryId);
  $lexem = Model::factory('Lexem')
         ->table_alias('l')
         ->select('l.*')
         ->join('EntryLexem', ['l.id', '=', 'el.lexemId'], 'el')
         ->where('el.entryId', $entry->id)
         ->find_one();
  $tags[] = [
    'id' => $line->id,
    'label' => $line->label,
    'textXCoord' => $line->textXCoord,
    'textYCoord' => $line->textYCoord,
    'imgXCoord' => $line->imgXCoord,
    'imgYCoord' => $line->imgYCoord,
    'lexem' => $lexem ? $lexem->formNoAccent : '',
    'entry' => $entry->description,
  ];
}

if($usage == 'table') {
  $resp = array('total' => ceil($total / $limit), 'page' => $page, 'records' => $total, 'rows' => $tags);

} else if($usage == 'gallery') {
  $resp = array('dims' => $dims, 'tags' => $tags);
}

echo json_encode($resp);
?>

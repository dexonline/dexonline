<?php
require_once '../../lib/Core.php';

$oper = Request::get('oper');
$id = Request::get('id');

$line = VisualTag::get_by_id($id);
$v = Visual::get_by_id($line->imageId ?? 0);

if ($line && $v) {
  switch ($oper) {
    case 'del':
      Log::notice("Deleted visual tag {$line->id} ({$line->label}) for image {$line->imageId}");
      $line->delete();
      break;

    case 'edit':
      $line->labelX = Request::get('labelX');
      $line->labelY = Request::get('labelY');
      $line->tipX = Request::get('tipX');
      $line->tipY = Request::get('tipY');
      $line->label = Request::get('label');
      $line->save();
      Log::notice("Edited visual tag {$line->id} ({$line->label}) for image {$line->imageId}");
      break;
  }

  $resp = [
    'success' => true,
    'tagInfo' => $v->getTagInfo(),
  ];

} else {

  $resp = [
    'success' => false,
    'msg' => 'Etichetă sau imagine incorectă.',
  ];

}

header('Content-Type: application/json');
echo json_encode($resp);

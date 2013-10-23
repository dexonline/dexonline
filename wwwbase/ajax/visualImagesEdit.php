<?php

require_once('../../phplib/util.php');

$oper = util_getRequestParameter('oper');
$id = util_getRequestParameter('id');

switch($oper) {
  case 'del':
    $line = Visual::get_by_id($id);
    if(!empty($line)) {
      $line->delete();
    }
    break;

  default:
    break;
}
?>

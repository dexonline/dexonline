<?php
require_once("../../phplib/util.php");

$id = util_getRequestParameter('id');

if (StringUtil::startsWith($id, '@')) {
  print json_encode(substr($id, 1) . ' (cuvânt nou)');
} else {
  $l = Lexem::get_by_id($id);
  print json_encode((string)$l);
}

?>

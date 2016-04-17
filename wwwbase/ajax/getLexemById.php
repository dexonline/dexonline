<?php
require_once("../../phplib/util.php");

$id = util_getRequestParameter('id');

if (StringUtil::startsWith($id, '@')) {
  $data = [
    'id' => $id,
    'text' => substr($id, 1) . ' (cuvânt nou)',
    'consistentAccent' => true,
    'hasParadigm' => true,
  ];
} else {
  $l = Lexem::get_by_id($id);

  $data = [
    'id' => $id,
    'text' => (string)$l,
    'consistentAccent' => $l->consistentAccent,
    'hasParadigm' => !$l->hasModelType('T'),
  ];
}

print json_encode($data);

?>

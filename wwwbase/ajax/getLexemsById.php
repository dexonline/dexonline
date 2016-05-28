<?php
require_once("../../phplib/util.php");

// Takes a JSON-encoded list of ids

$jsonIds = util_getRequestParameter('q');
$ids = json_decode($jsonIds);
$data = [];

foreach ($ids as $id) {
  if (StringUtil::startsWith($id, '@')) {
    $data[] = [
      'id' => $id,
      'text' => substr($id, 1) . ' (cuvânt nou)',
      'consistentAccent' => true,
      'hasParadigm' => true,
    ];
  } else {
    $l = Lexem::get_by_id($id);

    if ($l) {
      $data[] = [
        'id' => $id,
        'text' => (string)$l,
        'consistentAccent' => $l->consistentAccent,
        'hasParadigm' => $l->hasParadigm(),
      ];
    } else {
      $data[] = [
        'id' => 0,
        'text' => '',
      ];
    }
  }
}

header('Content-Type: application/json');
print json_encode($data);

?>

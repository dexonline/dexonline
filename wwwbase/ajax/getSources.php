<?php

require_once("../../phplib/util.php");
util_assertModerator(PRIV_STRUCT | PRIV_EDIT);
util_assertNotMirror();

$query = Request::get('term');
// Latin alphabet comparisons - allow Ş or S instead of Ș
$sources = Model::factory('Source')
         ->where_raw("shortName collate utf8_general_ci like '{$query}%'")
         ->limit(10)
         ->find_many();

$resp = [ 'results' => [] ];
foreach ($sources as $s) {
  $resp['results'][] = [
    'id' => $s->id,
    'text' => $s->shortName,
  ];
}

header('Content-Type: application/json');
echo json_encode($resp);

?>

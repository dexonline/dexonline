<?php
require_once('../../phplib/util.php');

$query = util_getRequestParameter('term');

$field = StringUtil::hasDiacritics($query) ? 'formNoAccent' : 'formUtf8General';

$lexems = Model::factory('Lexem')->where_like($field, "{$query}%")->order_by_asc('formNoAccent')->limit(10)->find_many();

$resp = array('more' => 'false', 'results' => array());
foreach($lexems as $lexem) {
  $resp['results'][] = array('id' => $lexem->id, 'text' => (string)$lexem->formUtf8General, 'description' => (string)$lexem->description);
}

echo json_encode($resp);
?>

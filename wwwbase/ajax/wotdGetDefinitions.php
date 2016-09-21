<?php

require_once("../../phplib/util.php");
util_assertModerator(PRIV_WOTD | PRIV_EDIT);
util_assertNotMirror();

$query = Request::get('term');
$definitions = Model::factory('Definition')->where('status', Definition::ST_ACTIVE)->where_like('lexicon', "{$query}%")
  ->order_by_asc('lexicon')->limit(20)->find_many();
$resp = array('results' => array());
foreach ($definitions as $definition){
  $source = Source::get_by_id($definition->sourceId);
  $resp['results'][] = array('id' => $definition->id,
                             'lexicon' => $definition->lexicon,
                             'text' => mb_substr($definition->internalRep, 0, 80),
                             'source' => $source->shortName);
}

header('Content-Type: application/json');
echo json_encode($resp);

?>

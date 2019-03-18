<?php

require_once '../../lib/Core.php';
User::mustHave(User::PRIV_WOTD | User::PRIV_EDIT);

$query = Request::get('term');
$definitions = Model::factory('Definition')
  ->table_alias('d')
  ->select('d.*')
  ->join('Source', ['d.sourceId', '=', 's.id'], 's')
  ->where('d.status', Definition::ST_ACTIVE)
  ->where_like('d.lexicon', "{$query}%")
  ->order_by_asc('d.lexicon')
  ->order_by_asc('s.displayOrder')
  ->limit(20)
  ->find_many();

$resp = ['results' => []];
foreach ($definitions as $d) {
  $source = Source::get_by_id($d->sourceId);
  $preview = Str::shorten($d->internalRep, 100);
  $html = Str::htmlize($preview, $d->sourceId)[0];

  $resp['results'][] = [
    'id' => $d->id,
    'lexicon' => $d->lexicon,
    'html' => $html,
    'source' => $source->shortName,
  ];
}

header('Content-Type: application/json');
echo json_encode($resp);

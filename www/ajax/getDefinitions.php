<?php

require_once '../../lib/Core.php';
User::mustHave(User::PRIV_WOTD | User::PRIV_EDIT);

$query = Request::get('term');
$definitions = Model::factory('Definition')
             ->where('status', Definition::ST_ACTIVE)
             ->where_like('lexicon', "{$query}%")
             ->order_by_asc('lexicon')
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

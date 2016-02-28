<?php
require_once("../../phplib/util.php");

$id = util_getRequestParameter('id');
$term = util_getRequestParameterWithDefault('term', (1 << NUM_PRIVILEGES) - 1);
$priv = util_getRequestParameter('priv');

if ($id) {
  $users = [User::get_by_id($id)];
} else if ($term && $priv) {
  $users = Model::factory('User')
         ->where_any_is([['nick' => "%{$term}%"],
                         ['name' => "%{$term}%"],
                         ['email' => "%{$term}%"]],
                        'like')
         ->where_raw("moderator & {$priv}")
         ->order_by_asc('nick')
         ->limit(10)
         ->find_many();
} else {
  $users = [];
}

$resp = ['results' => []];
foreach ($users as $u) {
  $resp['results'][] = ['id' => $u->id,
                        'text' => "{$u->nick} ({$u->name})"];
}
print json_encode($resp);

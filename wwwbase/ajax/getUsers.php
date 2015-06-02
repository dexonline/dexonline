<?php
require_once("../../phplib/util.php");
$resp = array();
if (util_isModerator(PRIV_ADMIN)) {
  /* Set a hard limit of 100 records to avoid abuse */
  $hard_limit = 100;
  $q = util_getRequestParameter('q');
  $limit = util_getRequestIntParameterWithDefault('limit', 10);
  $nick = util_getRequestIntParameterWithDefault('nick', 0);
  $privilege = util_getRequestIntParameterWithDefault('privilege', 0);
  if ($limit > $hard_limit) {
    $limit = $hard_limit;
  }
  $users = Model::factory('User')
    ->where_gte('moderator', $privilege)
    ->where_like('name', '%' . $q . '%')
    ->limit($limit)
    ->find_many();
  /** @var User $u */
  foreach ($users as $u) {
    $label = $nick ? $u->name . ' (' . $u->nick . ')' : $u->name;
    $resp[] = array('id' => $u->id(), 'text' => $label);
  }
}
print json_encode($resp);

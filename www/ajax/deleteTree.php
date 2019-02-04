<?php
require_once '../../lib/Core.php';
User::mustHave(User::PRIV_EDIT);

$id = Request::get('id');
$t = Tree::get_by_id($id);

if ($t) {
  $t->delete();
}

<?php
require_once("../../phplib/util.php");
User::require(User::PRIV_EDIT);
Util::assertNotMirror();

$id = Request::get('id');
$t = Tree::get_by_id($id);

if ($t) {
  $t->delete();
}

?>

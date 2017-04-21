<?php
require_once("../../phplib/Core.php");
User::mustHave(User::PRIV_EDIT);
Util::assertNotMirror();

$id = Request::get('id');
$t = Tree::get_by_id($id);

if ($t) {
  $t->delete();
}

?>

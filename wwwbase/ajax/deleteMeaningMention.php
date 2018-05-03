<?php
require_once("../../phplib/Core.php");
User::mustHave(User::PRIV_EDIT);
Util::assertNotMirror();

$id = Request::get('id');
$mention = Mention::get_by_id($id);

if ($mention) {
  $m = Meaning::get_by_id($mention->meaningId);
  $m->internalRep = str_replace("[{$mention->objectId}]", '', $m->internalRep);
  $m->process();
  $m->save();

  $mention->delete();
}

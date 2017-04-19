<?php
require_once("../../phplib/Core.php");
User::require(User::PRIV_EDIT);
Util::assertNotMirror();

$typoId = Request::get('id');
$typo = Typo::get_by_id($typoId);
if ($typo) {
  Log::debug("Ignored typo {$typo->id} ({$typo->problem})");
  $typo->delete();
}

?>

<?php
require_once("../../phplib/util.php");
util_assertModerator(PRIV_EDIT);
util_assertNotMirror();

$typoId = util_getRequestParameter('id');
$typo = Typo::get_by_id($typoId);
if ($typo) {
  Log::debug("Ignored typo {$typo->id} ({$typo->problem})");
  $typo->delete();
}

?>

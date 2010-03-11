<?
require_once("../../phplib/util.php");
util_assertModeratorStatus();
util_assertNotMirror();

$typoId = util_getRequestParameter('id');
$typo = new Typo();
$typo->load("id = {$typoId}");
if ($typo && $typo->id) {
  $typo->delete();
}

?>

<?
require_once("../../phplib/util.php");
util_assertModerator(PRIV_EDIT);
util_assertNotMirror();

$typoId = util_getRequestParameter('id');
$typo = new Typo();
$typo->load("id = {$typoId}");
if ($typo && $typo->id) {
  $typo->delete();
}

?>

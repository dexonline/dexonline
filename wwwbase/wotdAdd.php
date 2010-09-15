<?

require_once("../phplib/util.php");
require_once("../phplib/modelObjects.php");
util_assertModerator(PRIV_WOTD);
util_assertNotMirror();

$defId = util_getRequestParameter('defId');

$wotd = new WordOfTheDay();
$status = $wotd->getStatus($defId);

if (is_null($status)) {
	$wotd->defId = $defId;
	$wotd->priority = 0;
	$wotd->save();
	log_userLog("Added new word of the day: {$wotd->id} - the definition with the id {$wotd->defId}");
}

$where_to_go = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '/';
header("Location: {$where_to_go}");
?>

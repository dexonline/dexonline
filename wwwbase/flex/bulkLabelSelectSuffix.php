<?
require_once("../../phplib/util.php"); 

$dbResult = db_selectSuffixesAndCountsForTemporaryLexems();
$stats = array();

while ($dbRow = mysql_fetch_assoc($dbResult)) {
  $stats[] = array($dbRow['s'], $dbRow['c']);
}

smarty_assign('stats', $stats);
smarty_assign('sectionTitle', 'Alegere sufix');
smarty_assign('recentLinks', RecentLink::loadForUser());
smarty_displayWithoutSkin('flex/bulkLabelSelectSuffix.ihtml');

?>

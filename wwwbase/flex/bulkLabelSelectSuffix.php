<?php
require_once("../../phplib/util.php"); 

// Select suffixes and counts for temporary lexems.
$dbResult = db_execute("select reverse(substring(reverse, 1, 4)) as s, count(*) as c from Lexem where modelType = 'T' " .
                       "group by s having c >= 5 order by c desc, s");
$stats = array();
while (!$dbResult->EOF) {
  $stats[] = array($dbResult->fields['s'], $dbResult->fields['c']);
  $dbResult->MoveNext();
}

smarty_assign('stats', $stats);
smarty_assign('sectionTitle', 'Alegere sufix');
smarty_assign('recentLinks', RecentLink::loadForUser());
smarty_displayWithoutSkin('flex/bulkLabelSelectSuffix.ihtml');

?>

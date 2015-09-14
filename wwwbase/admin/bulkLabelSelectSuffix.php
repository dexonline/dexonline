<?php
require_once("../../phplib/util.php"); 

// Select suffixes and counts for temporary lexems.
$dbResult = db_execute("select reverse(substring(reverse, 1, 4)) as s, count(*) as c " .
                       "from Lexem join LexemModel on lexemId = Lexem.id " .
                       "where modelType = 'T' " .
                       "group by s having c >= 5 order by c desc, s", PDO::FETCH_ASSOC);
$stats = array();
foreach ($dbResult as $row) {
  $stats[] = array($row['s'], $row['c']);
}

SmartyWrap::assign('stats', $stats);
SmartyWrap::assign('sectionTitle', 'Alegere sufix pentru etichetare asistată');
SmartyWrap::assign('recentLinks', RecentLink::loadForUser());
SmartyWrap::displayAdminPage('admin/bulkLabelSelectSuffix.tpl');

?>

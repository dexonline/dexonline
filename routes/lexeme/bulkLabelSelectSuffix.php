<?php

// Select suffixes and counts for temporary lexemes.
$dbResult = DB::execute("select reverse(substring(reverse, 1, 4)) as s, count(*) as c " .
                       "from Lexeme " .
                       "where modelType = 'T' " .
                       "group by s having c >= 5 order by c desc, s", PDO::FETCH_ASSOC);
$stats = [];
foreach ($dbResult as $row) {
  $stats[] = [$row['s'], $row['c']];
}

Smart::assign('stats', $stats);
Smart::addResources('admin');
Smart::display('lexeme/bulkLabelSelectSuffix.tpl');

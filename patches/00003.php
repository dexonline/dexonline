<?php

// This patch fixes a typo in patch 00002, where line 20 says "$definition" instead of "$def".
// As a result, all the definition from SourceId 25 need to be re-htmlized.

$dbResult = mysql_query('select * from Definition where SourceId = 25');

$count = 0;
while ($dbRow = mysql_fetch_assoc($dbResult)) {
  $def = Definition::createFromDbRow($dbRow);
  $htmlRep = text_htmlize($def->internalRep);
  if ($htmlRep !== $def->htmlRep) {
    $def->htmlRep = $htmlRep;
    $def->save();
    $count++;
  }
}

print "$count definitions converted.\n";


?>

<?php

// We changed the code generated for spaced text. Therefore, all definitions containing % signs need to be re-htmlized

$dbResult = mysql_query('select * from Definition where InternalRep like "%\\%%"');

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

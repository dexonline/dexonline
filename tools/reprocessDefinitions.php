<?php
require_once "../phplib/util.php";
$dbResult = db_execute("select * from Definition where status = 0 and sourceId in (1, 2, 3, 4, 5) order by id");

$i = 0;
$modified = 0;
while (!$dbResult->EOF) {
  $def = new Definition();
  $def->set($dbResult->fields);
  $newRep = text_internalizeDefinition($def->internalRep, $def->sourceId);
  $newHtmlRep = text_htmlize($newRep, $def->sourceId);
  if ($newRep !== $def->internalRep || $newHtmlRep !== $def->htmlRep) {
    $modified++;
    $def->internalRep = $newRep;
    $def->htmlRep = $newHtmlRep;
    $def->save();
  }
  $dbResult->MoveNext();
  $i++;
  if ($i % 1000 == 0) {
    print "$i definitions reprocessed, $modified modified.\n";
  }
}
print "$i definitions reprocessed, $modified modified.\n";

?>

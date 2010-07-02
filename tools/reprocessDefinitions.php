<?
require_once "../phplib/util.php";
$dbResult = db_execute("select * from Definition where status = 0 order by id");

$f1 = fopen("/tmp/orig.txt", "w");
$f2 = fopen("/tmp/new.txt", "w");

$i = 0;
$modified = 0;
while (!$dbResult->EOF) {
  $def = new Definition();
  $def->set($dbResult->fields);
  $newRep = text_internalizeDefinition($def->internalRep);
  if ($newRep !== $def->internalRep) {
    if (trim($newRep) !== trim($def->internalRep)) {
      fwrite($f1, "{$def->id} {$def->internalRep}\nOK{$def->id}\n");
      fwrite($f2, "{$def->id} {$newRep}\nOK{$def->id}\n");
      $modified++;
    }
    $def->internalRep = $newRep;
    $definition->htmlRep = text_htmlize($newRep);
    $def->save();
  }
  $dbResult->MoveNext();
  $i++;
  if ($i % 10000 == 0) {
    print "$i definitions reprocessed, $modified modified.\n";
  }
}
print "$i definitions reprocessed, $modified modified.\n";

fclose($f1);
fclose($f2);

?>

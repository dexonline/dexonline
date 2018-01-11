<?php
require_once __DIR__ . '/../phplib/Core.php';
$dbResult = DB::execute("select * from Definition where status = 0 and sourceId in (33) order by id", PDO::FETCH_ASSOC);

$i = 0;
$modified = 0;
foreach ($dbResult as $row) {
  $def = Model::factory('Definition')->create($row);
  $newRep = Str::sanitize($def->internalRep, $def->sourceId);
  $newHtmlRep = Str::htmlize($newRep, $def->sourceId);
  if ($newRep !== $def->internalRep || $newHtmlRep !== $def->htmlRep) {
    $modified++;
    $def->internalRep = $newRep;
    $def->htmlRep = $newHtmlRep;
    $def->save();
  }
  $i++;
  if ($i % 1000 == 0) {
    print "$i definitions reprocessed, $modified modified.\n";
  }
}
print "$i definitions reprocessed, $modified modified.\n";

?>

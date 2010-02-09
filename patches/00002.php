<?php

define('NOTE', ' ?[(]?Notă: Definiția este preluată din Dicționar enciclopedic vol\. .*, Editura Enciclopedică, ....[)]\.?$');

$query = "select * from Definition where InternalRep rlike '" . NOTE . "'";
$dbResult = mysql_query($query);

$count = 0;
while ($dbRow = mysql_fetch_assoc($dbResult)) {
  $def = Definition::createFromDbRow($dbRow);

  $pos = strpos($def->internalRep, 'Notă:');
  $rep = substr($def->internalRep, 0, $pos);
  if (text_endsWith($rep, '(')) {
    $rep = substr($rep, 0, -1);
  }
  $rep = trim($rep);

  $def->internalRep = $rep;
  $definition->htmlRep = text_htmlize($def->internalRep);
  $def->sourceId = 25;
  $def->save();

  $count++;
}

print "$count definitions modified.\n";

?>

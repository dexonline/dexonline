<?php

require_once("../../phplib/util.php");

$lexems = db_find(new Lexem(), 'restriction like "%S%" or restriction like "%P%"');
$count = count($lexems);

foreach($lexems as $lexem) {
  print "Regenerez {$lexem->form} {$lexem->modelType}{$lexem->modelNumber}{$lexem->restriction} ($count rămase)\n";
  $lexem->regenerateParadigm();
  $count--;
}

?>
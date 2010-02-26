<?php

// Relabel all lexems ending in *fobie or *tomie from F134S to F134

$dbResult = mysql_query('select * from lexems where lexem_neaccentuat like "%fobie" or lexem_neaccentuat like "%tomie" order by lexem_model_type, lexem_model_no, lexem_restriction');

$count = 0;
while ($dbRow = mysql_fetch_assoc($dbResult)) {
  $count++;
  $lexem = Lexem::createFromDbRow($dbRow);
  $modelString = $lexem->modelType . $lexem->modelNumber . $lexem->restriction;
  printf("%3d. %-22s %-5s -- ", $count, $lexem->form, $modelString);
  if ($modelString == 'F134') {
    print "nimic de făcut\n";
  } elseif ($modelString == 'F134S') {
    $lexem->restriction = '';
    $lexem->save();
    $lexem->regenerateParadigm();
    print "am eliminat restricția S\n";
  } else {
    print "model necunoscut, nu mă ating\n";
  }
}

?>

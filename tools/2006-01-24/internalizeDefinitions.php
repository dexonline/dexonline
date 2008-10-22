<?php
require_once("../../phplib/util.php");

$result = logged_query("select * from words");
$count = 0;

while ($row = mysql_fetch_assoc($result)) {
  $def = $row['def'];
  //  echo "DEF: [$def]\n";
  $def = text_internalizeDefinition($def);
  //  echo "INT: [$def]\n";
  $htmlDef = text_htmlize($def);
  //   echo "HTM: [$htmlDef]\n";
  $dname = text_internalizeDname($row['dname']);
  //  echo "DNM: [$dname]\n";

  // TODO: Internalize the dname too, fix dname search

  logged_query("update words set def = '" . addslashes($def) . "', " .
 	       "htmlDef = '" . addslashes($htmlDef) . "', " .
 	       "dname = '" . addslashes($dname) . "' " .
  	       "where counter = " . $row['counter']);
  $count++;
  if ($count % 1000 == 0) {
    echo "Processed $count definitions.\n";
  }
}

echo "Done! Processed $count definitions.\n";

?>

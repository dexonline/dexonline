<?php
require_once("../../phplib/util.php");

$result = logged_query("select * from words " .
                       "where def like '%<%' or def like '%>%'");
$count = 0;
while ($row = mysql_fetch_assoc($result)) {
  $def = $row['def'];
  $htmlDef = text_htmlize($def);

  logged_query("update words set htmlDef = '" . addslashes($htmlDef) . "' " .
               "where counter = " . $row['counter']);
  $count++;
  if ($count % 50 == 0) {
    echo "Processed $count definitions.\n";
  }
}

echo "Done! Processed $count definitions.\n";

?>

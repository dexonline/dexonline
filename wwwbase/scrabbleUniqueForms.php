<?
require_once("../phplib/util.php");
ini_set('max_execution_time', '3600');
ini_set("memory_limit", "128000000");

$query = "select InflectedForm.formNoAccent from InflectedForm, lexems " .
  "where lexemId = lexem_id " .
  "and lexem_is_loc " .
  "and char_length(formNoAccent) between 2 and 15";
$dbResult = mysql_query($query);

ob_start();

while ($dbRow = mysql_fetch_row($dbResult)) {
  print "{$dbRow[0]}\r\n";
}

$s = ob_get_contents();
ob_end_clean();

$s = text_unicodeToLatin(text_unicodeToUpper($s));

$fileName = tempnam('/tmp', 'unique_');
$fileName2 = tempnam('/tmp', 'unique_');
$file = fopen($fileName, 'w');
fwrite($file, $s);
fclose($file);

os_executeAndAssert("sort -u $fileName -o $fileName2");

header('Content-type: text/plain');
print file_get_contents($fileName2);

os_executeAndAssert("rm -f $fileName $fileName2");


?>

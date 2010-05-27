<?
require_once("../phplib/util.php");
ini_set('max_execution_time', '3600');
ini_set("memory_limit", "128000000");

$query = "select I.formNoAccent from InflectedForm I, Lexem L where I.lexemId = L.id and L.isLoc and char_length(I.formNoAccent) between 2 and 15";
$dbResult = mysql_query($query);

$fileName = tempnam('/tmp', 'unique_');
$file = fopen($fileName, 'w');
while ($dbRow = mysql_fetch_row($dbResult)) {
  fwrite($file, "{$dbRow[0]}\r\n");
}
fclose($file);

$s = file_get_contents($fileName);
$s = text_unicodeToLatin($s);
$s = strtoupper($s);
$file = fopen($fileName, 'w');
fwrite($file, $s);
fclose($file);

$fileName2 = tempnam('/tmp', 'unique_');
os_executeAndAssert("sort -u $fileName -o $fileName2");

header('Content-type: text/plain');
print file_get_contents($fileName2);
os_executeAndAssert("rm -f $fileName $fileName2");

?>

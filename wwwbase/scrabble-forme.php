<?php
require_once("../phplib/util.php");
ini_set('max_execution_time', '3600');
ini_set("memory_limit", "128000000");

$query = "select I.formNoAccent from InflectedForm I, Lexem L, Model M, ModelDescription MD, ModelType MT " .
  "where I.lexemId = L.id and L.modelType = MT.code and MT.canonical = M.modelType and L.modelNumber = M.number and M.id = MD.modelId " .
  "and MD.inflectionId = I.inflectionId and MD.variant = I.variant and MD.applOrder = 0 and L.isLoc and MD.isLoc " .
  "and char_length(I.formNoAccent) between 2 and 15";
$dbResult = mysql_query($query);

$fileName = tempnam('/tmp', 'unique_');
$file = fopen($fileName, 'w');
while ($dbRow = mysql_fetch_row($dbResult)) {
  fwrite($file, "{$dbRow[0]}\r\n");
}
fclose($file);

$s = file_get_contents($fileName);
$s = StringUtil::unicodeToLatin($s);
$s = strtoupper($s);
$file = fopen($fileName, 'w');
fwrite($file, $s);
fclose($file);

$fileName2 = tempnam('/tmp', 'unique_');
OS::executeAndAssert("sort -u $fileName -o $fileName2");

header('Content-type: text/plain');
print file_get_contents($fileName2);
OS::executeAndAssert("rm -f $fileName $fileName2");

?>

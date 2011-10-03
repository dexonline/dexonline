<?php
require_once("common.php");
DEFINE('FILE_NAME', '/tmp/loc4-p.html');
DEFINE('EXPECTED_MODELS', 91);
$inflections = array(41, 42, 43, 44, 45, 46, 47, 48);
list($verbose, $fileName) = parseArguments();
$data = readAndFormatFile($fileName);

$numModels = 0;

// Skip five header rows
captureTr(); captureTr(); captureTr(); captureTr(); captureTr();

$buf = captureTr();
while (count($buf)) {
  $cells = $buf;

  // We might need to read a second row sometimes. The second row does
  // not contain the model number, so the columns are shifted by 1.
  $buf = captureTr();
  if (count($buf) && !preg_match('/\d+\./', $buf[0])) {
    foreach ($buf as $index => $extra) {
      $cells[$index + 1] .= $extra;
    }
    $buf = captureTr();
  }

  assert(preg_match('/\d+\./', $cells[0]));
  assert(count($cells) == 9);
  $modelNumber = substr($cells[0], 0, strlen($cells[0]) - 1);
  $forms = array_splice($cells, 1, 8);

  dprintArray($forms, $modelNumber);
  saveCommonModel('P', $modelNumber, $forms, '', $inflections);
  $numModels++;
}

assertEquals(EXPECTED_MODELS, $numModels);
?>

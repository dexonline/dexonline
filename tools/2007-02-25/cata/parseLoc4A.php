<?
// Expected: 257 models. Model 666 appears twice, but one of the instances has
// "display: none". Also, note models 657 and 657'.
require_once("common.php");
DEFINE('FILE_NAME', '/tmp/loc4-a.html');
DEFINE('EXPECTED_MODELS', 115);
$inflections = array(25, 26, 27, 28, 29, 30, 31, 32,
                     33, 34, 35, 36, 37, 38, 39, 40);
list($verbose, $fileName) = parseArguments();
$data = readAndFormatFile($fileName);

$numModels = 0;

// Skip five header rows
captureTr(); captureTr(); captureTr(); captureTr(); captureTr();

$cells = captureTr();
while (count($cells)) {
  // The first two cells are the model number and 'm.'
  assert(substr($cells[0], -1) == '.');
  assert($cells[1] == 'm.');
  // We might have one extra cell called 'Alternanţa'
  assert(count($cells) == 10 || count($cells) == 11);
  $modelNumber = substr($cells[0], 0, strlen($cells[0]) - 1);
  $transf = (count($cells) == 11) ? $cells[10] : '';
  $cells = array_splice($cells, 2, 8);

  $cells2 = captureTr();
  assert($cells2[0] == 'f.');
  $cells2 = array_splice($cells2, 1);

  $forms = array_merge($cells, $cells2);
  assert(count($forms) == 16);

  dprintArray($forms, $modelNumber, $transf);
  if ($transf) {
    dprint("Transform: $transf");
  }
  saveCommonModel('A', $modelNumber, $forms, $transf, $inflections);
  $numModels++;
  $cells = captureTr();
}

assertEquals(EXPECTED_MODELS, $numModels);

?>

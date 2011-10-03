<?php
// Expected: 257 models. Model 666 appears twice, but one of the instances has
// "display: none". Also, note models 657 and 657'.
require_once("common.php");
DEFINE('FILE_NAME', '/tmp/loc4-mfn.html');
DEFINE('EXPECTED_MODELS_M', 78);
DEFINE('EXPECTED_MODELS_F', 150);
DEFINE('EXPECTED_MODELS_N', 76);
list($verbose, $fileName) = parseArguments();
$data = readAndFormatFile($fileName);

// Skip four header rows
captureTr(); captureTr(); captureTr(); captureTr();

$inflectionsM = array(1, 2, 3, 4, 5, 6, 7, 8);
$inflectionsF = array(9, 10, 11, 12, 13, 14, 15, 16);
$inflectionsN = array(17, 18, 19, 20, 21, 22, 23, 24);
parseNounGroup('M', $inflectionsM, EXPECTED_MODELS_M);
captureTr();
parseNounGroup('F', $inflectionsF, EXPECTED_MODELS_F);
captureTr();
parseNounGroup('N', $inflectionsN, EXPECTED_MODELS_N);

/*************************************************************************/

function parseNounGroup($modelType, $inflections, $expectedModels) {
  $numModels = 0;

  $buf = captureTr();
  while (count($buf)) {
    $cells = $buf;
    
    // We might need to read a second row sometimes. The second row does
    // not contain the model number, so the columns are shifted by 1.
    $buf = captureTr();
    if (count($buf) && !preg_match('/\d+\./', $buf[0])) {
      assert(count($buf) == 2);
      $cells[1] .= $buf[0];
      $cells[5] .= $buf[1];
      $buf = captureTr();
    }
    
    assert(preg_match('/\d+\./', $cells[0]));
    assert(count($cells) == 9 || count($cells) == 10);
    $descr = (count($cells) == 10) ? $cells[9] : '';
    $modelNumber = substr($cells[0], 0, strlen($cells[0]) - 1);
    $forms = array_splice($cells, 1, 8);
    
    dprintArray($forms, $modelNumber);
    saveCommonModel($modelType, $modelNumber, $forms, $descr, $inflections);
    $numModels++;
  }

  assertEquals($expectedModels, $numModels);
}

?>

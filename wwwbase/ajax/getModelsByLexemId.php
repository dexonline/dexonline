<?php
require_once("../../phplib/util.php");

$id = util_getRequestParameter('id');
$data = Model::factory('LexemModel')
      ->table_alias('lm')
      ->select('lm.modelType')
      ->select('lm.modelNumber')
      ->select('lm.restriction')
      ->select('m.exponent')
      ->join('ModelType', ['lm.modelType', '=', 'mt.code'], 'mt')
      ->join('Model', 'mt.canonical = m.modelType and lm.modelNumber = m.number', 'm')
      ->where('lexemId', $id)
      ->find_many();

$results = [];
foreach ($data as $rec) {
  $results[] = ['modelType' => $rec->modelType,
                'modelNumber' => $rec->modelNumber,
                'restriction' => $rec->restriction,
                'exponent' => $rec->exponent];
}
print json_encode($results);

?>

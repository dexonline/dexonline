<?php
require_once("../../phplib/util.php");

$id = util_getRequestParameter('id');
$lm = LexemModel::get_by_lexemId_displayOrder($id, 1);
print json_encode(array('modelType' => $lm->modelType,
                        'modelNumber' => $lm->modelNumber,
                        'restriction' => $lm->restriction));

?>

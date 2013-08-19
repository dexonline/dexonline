<?php
require_once("../../phplib/util.php");

$id = util_getRequestParameter('id');
$l = Lexem::get_by_id($id);
print json_encode(array('modelType' => $l->modelType,
                        'modelNumber' => $l->modelNumber,
                        'restriction' => $l->restriction));

?>

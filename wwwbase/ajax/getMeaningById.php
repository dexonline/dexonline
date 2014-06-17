<?php
require_once("../../phplib/util.php");

$id = util_getRequestParameter('id');
$m = Meaning::get_by_id($id);
$l = Lexem::get_by_id($m->lexemId);
$results = array('lexem' => $l->formNoAccent,
                 'breadcrumb' => $m->breadcrumb,
                 'htmlRep' => $m->htmlRep);
print json_encode($results);

?>

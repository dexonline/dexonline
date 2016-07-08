<?php
require_once("../../phplib/util.php");

$id = util_getRequestParameter('id');
$m = Meaning::get_by_id($id);
$t = Tree::get_by_id($m->treeId);
$results = array('description' => $t->description,
                 'breadcrumb' => $m->breadcrumb,
                 'htmlRep' => $m->htmlRep);
print json_encode($results);

?>

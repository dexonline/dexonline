<?php
require_once("../../phplib/Core.php");

$id = Request::get('id');
$m = Meaning::get_by_id($id);
$t = Tree::get_by_id($m->treeId);
$results = [
  'description' => $t->description,
  'breadcrumb' => $m->breadcrumb,
  'html' => $m->getHtml(),
];
print json_encode($results);

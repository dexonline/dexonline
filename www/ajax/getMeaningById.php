<?php
require_once '../../lib/Core.php';

$id = Request::get('id');
$m = Meaning::get_by_id($id);
$t = Tree::get_by_id($m->treeId);
$results = [
  'description' => $t->description,
  'breadcrumb' => $m->breadcrumb,
  'html' => HtmlConverter::convert($m),
];
print json_encode($results);

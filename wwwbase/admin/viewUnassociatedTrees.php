<?php
require_once("../../phplib/util.php"); 
User::require(User::PRIV_EDIT);
util_assertNotMirror();

$trees = Model::factory('Tree')
       ->table_alias('t')
       ->select('t.*')
       ->left_outer_join('TreeEntry', ['t.id', '=', 'te.treeId'], 'te')
       ->where_null('te.id')
       ->order_by_asc('t.description')
       ->find_many();

SmartyWrap::assign('trees', $trees);
SmartyWrap::addCss('admin', 'meaningTree');
SmartyWrap::display('admin/viewUnassociatedTrees.tpl');

?>

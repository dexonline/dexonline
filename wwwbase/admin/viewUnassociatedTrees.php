<?php
require_once '../../phplib/Core.php';
User::mustHave(User::PRIV_EDIT);

$trees = Model::factory('Tree')
       ->table_alias('t')
       ->select('t.*')
       ->left_outer_join('TreeEntry', ['t.id', '=', 'te.treeId'], 'te')
       ->where_null('te.id')
       ->order_by_asc('t.descriptionSort')
       ->find_many();

SmartyWrap::assign('trees', $trees);
SmartyWrap::addCss('admin', 'meaningTree');
SmartyWrap::display('admin/viewUnassociatedTrees.tpl');

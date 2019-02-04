<?php
require_once '../../lib/Core.php';
User::mustHave(User::PRIV_EDIT);

$trees = Model::factory('Tree')
       ->table_alias('t')
       ->select('t.*')
       ->left_outer_join('TreeEntry', ['t.id', '=', 'te.treeId'], 'te')
       ->where_null('te.id')
       ->order_by_asc('t.descriptionSort')
       ->find_many();

Smart::assign('trees', $trees);
Smart::addCss('admin', 'meaningTree');
Smart::display('admin/viewUnassociatedTrees.tpl');

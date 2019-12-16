<?php
User::mustHave(User::PRIV_ADMIN | User::PRIV_EDIT);

$allSources = Model::factory('Source')
  ->order_by_asc('displayOrder')
  ->find_many();

Smart::assign('allSources', $allSources);
Smart::display('pageIndex/list.tpl');

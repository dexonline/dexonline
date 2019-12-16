<?php

require_once '../../lib/Core.php';
User::mustHave(User::PRIV_EDIT);

$sourceId = Request::get('sourceId', 0);

if ($sourceId) {
  $abbrevs = Model::factory('PageIndex')
    ->where('sourceId', $sourceId)
    ->order_by_asc('page')
    ->find_many();
}

Smart::assign('sourceId', $sourceId);
Smart::assign('results', $abbrevs);
Smart::display('ajax/getPageIndex.tpl');

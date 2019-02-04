<?php

require_once '../../lib/Core.php';
User::mustHave(User::PRIV_ADMIN | User::PRIV_EDIT);

FlashMessage::add("Avertisment! În momentul editării unei abrevieri, prin schimbarea "
  . "formei acesteia, sunt afectate și celelalte definiții deja moderate "
  . "din acel dicționar.", 'warning');

$allSources = Model::factory('Source')
                     ->order_by_asc('displayOrder')
                     ->find_many();

SmartyWrap::assign('allSources', $allSources);
SmartyWrap::display('admin/abbrevList.tpl');

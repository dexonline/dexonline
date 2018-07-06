<?php

require_once("../../phplib/Core.php");
User::mustHave(User::PRIV_ADMIN);
Util::assertNotMirror();

FlashMessage::add("Avertisment! În momentul editării unei abrevieri, prin schimbarea "
  . "formei acesteia, sunt afectate și celelalte definiții deja moderate "
  . "din acel dicționar.", 'warning');

$allSources = Model::factory('Source')
                     ->order_by_asc('displayOrder')
                     ->find_many();

SmartyWrap::assign('allSources', $allSources);
SmartyWrap::display('admin/abbrevList.tpl');

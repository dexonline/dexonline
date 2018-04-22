<?php

require_once("../../phplib/Core.php");
User::mustHave(User::PRIV_EDIT);
Util::assertNotMirror();

$class = "success";
$message = "";

if (!User::can(User::PRIV_ADMIN)) {
  $message = "Avertisment! În momentul editării unei abrevieri, prin schimbarea formei acesteia, sunt afectate și celelalte definiții deja moderate din acel dicționar.";
  $class = "warning";
}

$allModeratorSources = Model::factory('Source')
                                ->where('canModerate', true)
                                ->order_by_asc('displayOrder')
                                ->find_many();

SmartyWrap::assign('msgClass', $class);
SmartyWrap::assign('message', $message);
SmartyWrap::assign('modUser', User::getActive());
SmartyWrap::assign('allModeratorSources', $allModeratorSources);
SmartyWrap::display('admin/abbrevEdit.tpl');

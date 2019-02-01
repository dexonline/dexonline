<?php
require_once '../phplib/Core.php';

$user = User::getActive();
$haveEuPlatescCredentials = Config::EU_PLATESC_MID && Config::EU_PLATESC_KEY;

SmartyWrap::assign('haveEuPlatescCredentials', $haveEuPlatescCredentials);
SmartyWrap::assign('defaultEmail', $user ? $user->email : '');
SmartyWrap::display('doneaza.tpl');

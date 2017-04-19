<?php
require_once("../phplib/util.php");

$user = Session::getUser();
$haveEuPlatescCredentials = Config::get('euplatesc.euPlatescMid') && Config::get('euplatesc.euPlatescKey');

SmartyWrap::assign('haveEuPlatescCredentials', $haveEuPlatescCredentials);
SmartyWrap::assign('defaultEmail', $user ? $user->email : '');
SmartyWrap::display('doneaza.tpl');

?>

<?php
$user = User::getActive();
$haveEuPlatescCredentials = Config::EU_PLATESC_MID && Config::EU_PLATESC_KEY;

Smart::assign('haveEuPlatescCredentials', $haveEuPlatescCredentials);
Smart::assign('defaultEmail', $user ? $user->email : '');
Smart::display('donation/donate.tpl');

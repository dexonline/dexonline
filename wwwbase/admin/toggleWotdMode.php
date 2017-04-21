<?php

require_once("../../phplib/Core.php");
User::mustHave(User::PRIV_WOTD);

Session::toggleWotdMode();

$target = isset($_SERVER['HTTP_REFERER'])
        ? $_SERVER['HTTP_REFERER']
        : Core::getWwwRoot();
Util::redirect($target);

?>

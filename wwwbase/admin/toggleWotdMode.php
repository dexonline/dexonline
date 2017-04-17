<?php

require_once("../../phplib/util.php");
User::require(User::PRIV_WOTD);

session_toggleWotdMode();

$target = isset($_SERVER['HTTP_REFERER'])
        ? $_SERVER['HTTP_REFERER']
        : util_getWwwRoot();
util_redirect($target);

?>

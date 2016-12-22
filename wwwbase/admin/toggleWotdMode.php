<?php

require_once("../../phplib/util.php");
util_assertModerator(PRIV_WOTD);

session_toggleWotdMode();

$target = isset($_SERVER['HTTP_REFERER'])
        ? $_SERVER['HTTP_REFERER']
        : util_getWwwRoot();
util_redirect($target);

?>

<?php

require_once("../phplib/util.php");
setlocale(LC_ALL, "ro_RO.utf8");

SmartyWrap::addCss('mill');
SmartyWrap::addJs('mill');
SmartyWrap::display("mill.tpl");
?>

<?php

require_once("../phplib/Core.php");
setlocale(LC_ALL, "ro_RO.utf8");

SmartyWrap::addJs('pixijs', 'seedrandom', 'cookie');
SmartyWrap::display("scramble.tpl");

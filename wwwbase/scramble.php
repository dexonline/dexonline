<?php

require_once("../phplib/Core.php");

SmartyWrap::addJs('pixijs', 'seedrandom', 'cookie');
SmartyWrap::display("scramble.tpl");

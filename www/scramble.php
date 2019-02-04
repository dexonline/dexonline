<?php

require_once '../lib/Core.php';

SmartyWrap::addJs('pixijs', 'seedrandom', 'cookie');
SmartyWrap::display("scramble.tpl");

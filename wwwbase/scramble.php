<?php

require_once("../phplib/Core.php");
setlocale(LC_ALL, "ro_RO.utf8");

SmartyWrap::assign('page_title', 'Omleta Cuvintelor');
SmartyWrap::addJs('pixijs', 'cookie');
SmartyWrap::display("scramble.tpl");
?>
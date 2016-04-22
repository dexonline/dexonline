<?php

require_once("../phplib/util.php");
setlocale(LC_ALL, "ro_RO.utf8");

SmartyWrap::assign('page_title', 'Omleta Cuvintelor');
SmartyWrap::addJs('jcanvas');
SmartyWrap::display("scramble.tpl");
?>
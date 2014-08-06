<?php

require_once("../phplib/util.php");
setlocale(LC_ALL, "ro_RO.utf8");

SmartyWrap::assign('page_title', 'Moara cuvintelor');
SmartyWrap::addCss('mill');
SmartyWrap::addJs('mill');
SmartyWrap::display("mill.ihtml");
?>

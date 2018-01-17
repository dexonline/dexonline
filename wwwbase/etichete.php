<?php
require_once("../phplib/Core.php");

SmartyWrap::assign('tags', Tag::loadTree());
SmartyWrap::addCss('admin');
SmartyWrap::display('etichete.tpl');

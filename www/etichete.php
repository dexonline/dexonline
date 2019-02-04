<?php
require_once '../lib/Core.php';

SmartyWrap::assign('tags', Tag::loadTree());
SmartyWrap::addCss('admin');
SmartyWrap::display('etichete.tpl');

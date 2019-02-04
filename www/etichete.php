<?php
require_once '../lib/Core.php';

Smart::assign('tags', Tag::loadTree());
Smart::addCss('admin');
Smart::display('etichete.tpl');

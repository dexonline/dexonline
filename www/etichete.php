<?php
require_once '../lib/Core.php';

Smart::assign('tags', Tag::loadTree());
Smart::addResources('admin');
Smart::display('etichete.tpl');

<?php
require_once("../phplib/Core.php");
$p = Request::get('p');

switch($p) {
  case '404':
    http_response_code(404);
    break;
  case 'contact': break;
  case 'links': break;
  default: exit;
}

SmartyWrap::display("$p.tpl");

<?php
require_once("../phplib/util.php");
$p = Request::get('p');

switch($p) {
  case '404':
    http_response_code(404);
    break;
  case 'contact': break;
  case 'links': break;
  case 'ads': break;
  default: exit;
}

SmartyWrap::display("$p.tpl");
?>

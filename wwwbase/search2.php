<?php
require_once("../phplib/util.php");

// Parse or initialize the GET/POST arguments
$scriptParameters = array("default" => "" /* default param */);
extract($scriptParameters, EXTR_PREFIX_ALL, "req");
extract($_REQUEST, EXTR_PREFIX_ALL, "req"); 

smarty_displayPageWithSkin('search2.ihtml');
?>

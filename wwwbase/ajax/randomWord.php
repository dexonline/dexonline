<?php
require_once("../../phplib/util.php");

$count = Model::factory('RandomWord')->count();
$choice = rand(0, $count - 1);
$rw = Model::factory('RandomWord')->limit(1)->offset($choice)->find_one();
echo $rw->cuv;

?>

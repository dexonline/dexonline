<?php
require_once("../../phplib/Core.php");

// The seq field is guaranteed to be incremental from 1 to <number of rows>
$count = Model::factory('RandomWord')->count();
$choice = rand(1, $count);
$rw = RandomWord::get_by_seq($choice);
echo $rw ? $rw->cuv : '';

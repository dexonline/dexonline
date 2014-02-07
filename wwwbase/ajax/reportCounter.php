<?php
require_once("../../phplib/util.php");
util_assertModerator(PRIV_EDIT);
util_assertNotMirror();

$reportId = util_getRequestParameter('report');
switch($reportId) {
case 'lexemsWithComments': echo Model::factory('Lexem')->where_not_equal('comment', '')->count(); break;
default: echo 'Necunoscut';
}

?>

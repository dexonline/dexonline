<?php

require_once("../phplib/util.php");
setlocale(LC_ALL, "ro_RO.utf8");

smarty_assign('page_title', 'Moara cuvintelor');
smarty_addCss('mill');
smarty_addJs('mill');
smarty_displayCommonPageWithSkin("mill.ihtml");
?>

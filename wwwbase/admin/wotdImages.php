<?php
require_once("../../phplib/util.php");
util_assertModerator(PRIV_WOTD);
util_assertNotMirror();
RecentLink::createOrUpdate('Word of the Day - Imagini');

SmartyWrap::assign('sectionTitle', 'Imagini pentru cuvântul zilei');
SmartyWrap::addCss('elfinder', 'jqueryui');
SmartyWrap::addJs('jquery', 'jqueryui', 'elfinder');
SmartyWrap::displayAdminPage('admin/wotdImages.ihtml');
?>

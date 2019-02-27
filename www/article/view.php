<?php

$title = Request::get('title');
$wikiTitle = WikiArticle::urlTitleToWikiTitle($title);
$wa = WikiArticle::get_by_title(addslashes($wikiTitle));

Smart::assign('wa', $wa);
Smart::assign('wikiTitles', WikiArticle::loadAllTitles());
Smart::addResources('tablesorter');
Smart::display('article/view.tpl');

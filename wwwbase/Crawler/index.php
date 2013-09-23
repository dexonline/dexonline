<?php

require_once '../../phplib/util.php';
require_once '../../phplib/serverPreferences.php';
require_once '../../phplib/db.php';
require_once '../../phplib/idiorm/idiorm.php';
require_once '../../phplib/idiorm/paris.php';


$rows = CrawledPage::getListOfDomains();


$options = array('all', 'most recent domain');
$last = end($rows);
$values = array('all', $last->domain);


foreach($rows as $obj) {

	array_push($options,$obj->domain);
	array_push($values,$obj->domain);
}

//var_dump($options);

SmartyWrap::assign('page_title', 'Romanian Crawler Log');

SmartyWrap::assign('values', $values);
SmartyWrap::assign('options', $options);

SmartyWrap::smartyDisplay('crawler/crawler.ihtml');

?>
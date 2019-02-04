<?php

require_once '../../lib/Core.php';

//User::require(User::PRIV_ADMIN);

$rows = CrawledPage::getListOfDomains();


$options = ['all', 'most recent domain'];
$last = end($rows);
$values = ['all', $last->domain];


foreach($rows as $obj) {

	array_push($options,$obj->domain);
	array_push($values,$obj->domain);
}

SmartyWrap::assign('values', $values);
SmartyWrap::assign('options', $options);

//SmartyWrap::display('crawler/crawler.tpl');
SmartyWrap::assign('jqueryLibPath', '../js/jquery-1.8.3.min.js');
SmartyWrap::displayWithoutSkin('crawler/crawler.tpl');

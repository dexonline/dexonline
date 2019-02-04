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

Smart::assign('values', $values);
Smart::assign('options', $options);

//Smart::display('crawler/crawler.tpl');
Smart::assign('jqueryLibPath', '../js/jquery-1.8.3.min.js');
Smart::displayWithoutSkin('crawler/crawler.tpl');

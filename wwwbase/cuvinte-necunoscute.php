<?php
require_once("../phplib/Core.php");
User::mustHave(User::PRIV_EDIT | User::PRIV_ADMIN);

$unknownWords = Model::factory('CrawlerUnknownWord')
              ->table_alias('uw')
              ->select('uw.*')
              ->select_expr('count(*)', 'count')
              ->group_by('word')
              ->order_by_desc('count')
              ->limit(100)
              ->find_many();

$numUnknownWords = DB::getSingleValue('select count(distinct word) from CrawlerUnknownWord');

SmartyWrap::assign('unknownWords', $unknownWords);
SmartyWrap::assign('numUnknownWords', $numUnknownWords);
SmartyWrap::display('cuvinte-necunoscute.tpl');

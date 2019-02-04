<?php
/*
 * Alin Ungureanu, 2013
 * alyn.cti@gmail.com
 */
require_once '../../lib/Core.php';

class FetchCrawlerStatus {
	
	function getStatusPerHttpCode($domain = null) {

		$crawledPage = Model::factory('CrawledPage');
		$link = Model::factory('Link')->create();

		$data = '';
		$crawledPageFilter = '';

		if (isset($domain)) {

			$crawledPageFilter = " where url like 'http://$domain%'";
		}

		$rows = $crawledPage->raw_query("Select httpStatus, count(httpStatus) as cnt from CrawledPage $crawledPageFilter Group by httpStatus;")->find_many();
		
		foreach($rows as $row) {

			$data .= $row->httpStatus . ': <span class="httpStatusResult">' . $row->cnt .'</span><br>';
		}

		return $data;
	}

	function getTotalNumber($domain = null) {


		$crawledPage = Model::factory('CrawledPage');
		$link = Model::factory('Link');

		$data = '';
		$linkFilter = '';
		$crawledPageFilter = '';

		if (isset($domain)) {
			
			$linkFilter = " where domain like '$domain'";
			$crawledPageFilter = " where url like 'http://$domain%'";
			
		}

		$row = $crawledPage->raw_query("Select count(*) as cnt from CrawledPage $crawledPageFilter;")->find_one()->cnt;
		$data .= 'Total processed pages: <span class="totalProcessed">'. $row . '</span><br>';

		$row = $link->raw_query("Select count(*) as cnt from Link $linkFilter;")->find_one()->cnt;
		$data .= 'Total links discovered: <span class="totalLinks">'. $row . '</span><br>';

		return $data;
	}

}


if (isset($_POST['method']) && !empty($_POST['method'])) {


	$fetch = new FetchCrawlerStatus();





	if (isset($_POST['domain']) && $_POST['domain'] != 'all') {

		if ($_POST['method'] == 'fetch_total') {

			echo $fetch->getTotalNumber($_POST['domain']);
		}

		else if ($_POST['method'] == 'fetch_per_http_code') {

			echo $fetch->getStatusPerHttpCode($_POST['domain']);
		}
	}
	else if (isset($_POST['domain']) && $_POST['domain'] == 'all') {

		if ($_POST['method'] == 'fetch_total') {

			echo $fetch->getTotalNumber();
		}
		else if ($_POST['method'] == 'fetch_per_http_code') {

			echo $fetch->getStatusPerHttpCode();

		}
	}
}

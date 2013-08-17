<?php
/*
 * Alin Ungureanu, 2013
 * alyn.cti@gmail.com
 */
require_once 'AbstractCrawler.php';
require_once 'simple_html_dom.php';

class Crawler extends AbstractCrawler {

	//extrage textul fara cod html
	function getText($domNode) {
		//dump_html_tree($domNode);
		$this->plainText = $domNode->text();
	}
	//extrage textul cu cod html din nodul respectiv
	function extractText($domNode) {

		crawlerLog("extracting text");
		$this->getText($domNode);

		foreach($domNode->find("a") as $link) {

			$this->processLink($link->href);
		}
	}


	function startCrawling($startUrl) {
	
		crawlerLog("Started");


		$this->currentUrl = $this->urlPadding($startUrl);

		crawlerLog('FIRST START URL: '.$this->currentUrl);

		$this->urlResource = parse_url($this->currentUrl);

		//locatia curenta, va fi folosita pentru a nu depasi sfera
		//de exemplu vrem sa crawlam doar o anumita zona a site-ului
		$this->currentLocation = substr($startUrl, strpos($startUrl, ':') + 3);
		crawlerLog('domain start location: '.$this->currentLocation);

		$url = $startUrl;

		$justStarted = true;

		while(1) {

			//extrage urmatorul link neprelucrat din baza de date
			$url = $this->getNextLink();
			crawlerLog('current URL: ' . $url);
			//daca s-a terminat crawling-ul
			if ($url == null || $url == '') break;

			//download pagina
			$pageContent = $this->getPage($url);
			//setam url-ul curent pentru store in Database
			$this->currentUrl = $url;

			$this->setStorePageParams();

			//salveaza o intrare despre pagina curenta in baza de date
			$this->currentPageId = CrawledPage::savePage2DB($this->currentUrl, $this->httpResponse(), $this->rawPagePath, $this->parsedTextPath, $this->currentTimestamp);
			
			//daca pagina nu e in format html (e imagine sau alt fisier)
			//sau daca am primit un cod HTTP de eroare, sarim peste pagina acesta
			if (!$this->pageOk()) {
				continue;
			}
			
			try {
				//transforma pagina raw in simple_html_dom_node
				$this->dom = str_get_html($pageContent);
				//extrage continutul dintre tagurile <BODY> si </BODY>
				$this->body = $this->dom->find('body', 0, true);
				//extrage recursiv linkurile si textul din body
				$this->extractText($this->body);
				//salveaza pagina in 2 formate: raw html si clean text
				$this->saveCurrentPage();

				//cata memorie consuma
				$this->manageMemory();
				//niceness
				sleep(pref_getSectionPreference('crawler', 't_wait'));
			}
			catch (Exception $ex) {

				logException($ex);
			}

		}

		crawlerLog('Finished');
	}
}

/*
 *  Obiectul nu va fi creat daca acest fisier nu va fi fisier cautat
 */
if (strstr( $_SERVER['SCRIPT_NAME'], 'Crawler.php')) {

	$obj = new Crawler();
	//$obj->startCrawling("http://wiki.dexonline.ro/");
	$obj->startCrawling("http://www.romlit.ro");
}
?>
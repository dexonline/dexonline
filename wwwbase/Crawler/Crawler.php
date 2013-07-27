<?php
/*
 * Alin Ungureanu, 2013
 * alyn.cti@gmail.com
 */
require_once 'AbstractCrawler.php';
require_once 'simple_html_dom.php';

class Crawler extends AbstractCrawler {

	function __construct() {

		$this->plainText = '';
		$this->pageContent = '';

	}

	//extrage textul fara cod html
	function getText($domNode) {

		$this->plainText = $domNode->text();
	}
	//extrage textul cu cod html din nodul respectiv
	function extractText($domNode, $i) {

		crawlerLog("extracting text");
		$this->getText($domNode);

		foreach($domNode->find("a") as $link) {

			$this->processLink($link);
		}
	}


	function startCrawling($startUrl) {
	
		crawlerLog("Started");


		$this->currentUrl = $startUrl;

		//locatia curenta, va fi folosita pentru a nu depasi sfera
		//de exemplu vrem sa crawlam doar o anumita zona a site-ului
		$this->currentLocation = substr($startUrl, strpos($startUrl, ':') + 3);
		crawlerLog($this->currentLocation);

		$url = $startUrl;

		$justStarted = true;

		while(1) {

			//extrage urmatorul link neprelucrat din baza de date
			$url = $this->getNextLink();
			crawlerLog('current URL: ' . $url);
			//daca s-a terminat crawling-ul
			if ($url == null || $url == '') return;

			//download pagina
			$pageContent = $this->getPage($url);
			//setam url-ul curent pentru store in Database
			$this->currentUrl = $url;

			$this->setStorePageParams();

			//salveaza o intrare despre pagina curenta in baza de date
			$this->savePage2DB($this->currentUrl, $this->httpResponse(), $this->rawPagePath, $this->parsedTextPath);
			
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
				$this->extractText($this->body, 1);
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
	$obj->startCrawling("http://wiki.dexonline.ro");
}
?>
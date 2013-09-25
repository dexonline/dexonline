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
		
		$this->plainText = html_entity_decode(strip_tags($domNode->text()));
		//$this->plainText = str_replace(array('\t','\n',' ', '&nbsp;'), array('','.','',''),strip_tags($domNode->text()));
	}
	//extrage textul cu cod html din nodul respectiv
	function extractText($domNode) {

		crawlerLog("extracting text");
		$this->getText($domNode);

		foreach($domNode->find("a") as $link) {

			$this->processLink($link->href);
		}
	}

	function processPage($pageContent) {

		try {
			
			$html = str_get_html($pageContent);

			//reparam html stricat
			if (!$html->find('body', 0, true)) {

				$html = $this->fixHtml($html);
			}
			

			$this->extractText($html->find('body', 0, true));
			$this->saveCurrentPage();
			
			//cata memorie consuma
			//si eliberare referinte pierdute
			
			$html->clear();

			MemoryManagement::showUsage('before cleaning', true, 'KB');
			
			MemoryManagement::clean(true);

			MemoryManagement::showUsage('after cleaning', true, 'KB');
			//niceness
			sleep(pref_getSectionPreference('crawler', 't_wait'));
		}
		catch (Exception $ex) {

			logException($ex);
		}
	}

	function crawlDomain() {

		crawlerLog("Crawling: " . $this->getDomain($this->currentUrl) . " started");

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
			
			$this->processPage($pageContent);
		}

		crawlerLog("Crawling: " . $this->getDomain($this->currentUrl) . " finished");
	}


	function start() {
	
		crawlerLog("Crawler started");

		$this->domainsList = explode(PHP_EOL, file_get_contents("WhiteList.txt"));

		//start processing 
		$this->processWhiteList();

		crawlerLog('Crawler finished');
	}


	function processWhiteList() {

		$multipleLinesComment = false;

		foreach($this->domainsList as $startUrl) {
			
			$startUrl = trim($startUrl);

			//comentarii pe mai multe linii

			if (substr($startUrl, 0, 3) == '###') {
				//flip bit
				$multipleLinesComment ^= 1;
			}
			
			//comentarii sau linii goale
			if ($multipleLinesComment || substr($startUrl,0,1) == '#'
				|| !$startUrl)
				continue;


			//curatam url-ul
			$this->currentUrl = $this->urlPadding($startUrl);
			//impartim url-ul pe componente
			$this->urlResource = parse_utf8_url($this->currentUrl);

			//salvam startUrl in tabelul Link pentru a incepe extragerea,
			//startUrl nu va avea o pagina din care este descoperit
			//asa ca pagina crawledPageId va avea valoarea 0.
			Link::saveLink2DB($this->currentUrl, $this->getDomain($this->currentUrl), '0');

			//locatia curenta, va fi folosita pentru a nu depasi sfera
			//de exemplu vrem sa crawlam doar o anumita zona a site-ului
			$this->currentLocation = substr($this->currentUrl, 0);
			crawlerLog('domain start location: '.$this->currentLocation);

			$this->crawlDomain();
		}
		
	}

}

/*
 *  Obiectul nu va fi creat daca acest fisier nu va fi fisier cautat
 */
if (strstr( $_SERVER['SCRIPT_NAME'], 'Crawler.php')) {

	$obj = new Crawler();

	$obj->start();
}

?>
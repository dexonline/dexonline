<?php
/*
 * Alin Ungureanu, 2013
 * alyn.cti@gmail.com
 */
require_once '../../phplib/util.php';
require_once '../../phplib/serverPreferences.php';
require_once '../../phplib/db.php';
require_once '../../phplib/idiorm/idiorm.php';


require_once 'AppLog.php';


db_init();

abstract class AbstractCrawler {

	protected $ch;
	protected $pageContent;
	protected $dom;
	protected $body;
	protected $plainText;
	protected $info;
	protected $currentUrl;
	protected $currentTimestamp;
	protected $currentPageId;
	protected $rawPagePath;
	protected $parsedTextPath;

	protected $currentLocation;

	protected $urlResource;

	private $justStarted;

	//descarca pagina de la $url
	function getPage( $url) {

		$this->ch = curl_init();

		curl_setopt ($this->ch, CURLOPT_URL, $url);
		curl_setopt ($this->ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt ($this->ch, CURLOPT_USERAGENT, "Mozilla/5.0 (compatible; MSIE 10.6; Windows NT 6.1; Trident/5.0; InfoPath.2; SLCC1; .NET CLR 3.0.4506.2152; .NET CLR 3.5.30729; .NET CLR 2.0.50727) 3gpp-gba UNTRUSTED/1.0");
		curl_setopt ($this->ch, CURLOPT_TIMEOUT, 60);
		curl_setopt ($this->ch, CURLOPT_FOLLOWLOCATION, TRUE);
		curl_setopt ($this->ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($this->ch, CURLOPT_COOKIEFILE, 'cookie_jar');
		curl_setopt ($this->ch, CURLOPT_REFERER, $url);
		$this->pageContent = curl_exec($this->ch);
		$this->info = curl_getinfo($this->ch);

		if(!curl_errno($this->ch)) {
 			
 			$this->info = curl_getinfo($this->ch);
		}
		else{

			$this->info = array('http_code' => 404);
		}

		curl_close( $this->ch);
		return $this->pageContent;
	}

	//gaseste toate linkurile
	//le transforma in absolute daca sunt relative
	function processLink($link) {

		
		$url = $link->href;
		$canonicalUrl = null;
		if ($this->isRelativeLink($url)) {

			$url = $this->makeAbsoluteLink($url);
		}
		//daca ultimul caracter este '/', il eliminam
		//exemplu wiki.dexonline.ro nu wiki.dexonline.ro/
		if (substr($url, -1) == "/") $url = substr($url, 0, -1);

		$canonicalUrl = ''.$url;
		
		$this->urlResouce = parse_url($url);



		if (!strstr($url, $this->currentLocation)) return;


		$urlHash = $this->getLinkHash($url);

		$domain = $this->getDomain($url);

		$this->saveLink2DB($canonicalUrl, $domain, $urlHash, $this->currentPageId);
	}

	//adauga o intrare nou in tabelul Link
	function saveLink2DB($canonicalUrl, $domain, $urlHash, $crawledPageId) {

		//nu inseram acelasi link de 2 ori
		if (ORM::for_table('Link')->where('canonicalUrl', $canonicalUrl)->find_one()) {
			return;
		}

		try {
			$tableObj = ORM::for_table("Link");
			$tableObj->create();
			$tableObj->canonicalUrl = $canonicalUrl;
			$tableObj->domain = $domain;
			$tableObj->urlHash = $urlHash;
			$tableObj->crawledPageId = $crawledPageId;
			$tableObj->save();
		}
		catch(Exception $ex) {

			logException($ex);
		}
	}
	//salveaza informatiile despre pagina curent crawl-ata in tabelul CrawledPage
	function savePage2DB($url, $httpStatus, $rawPagePath, $parsedTextPath) {

		try {
			$tableObj = ORM::for_table("CrawledPage");
			$tableObj->create();
			$tableObj->timestamp = $this->currentTimestamp;
			$tableObj->url = $url;
			$tableObj->httpStatus = ''.$this->info["http_code"];
			$tableObj->rawPagePath = $rawPagePath;
			$tableObj->parsedTextPath = $parsedTextPath;
			$tableObj->save();

			$this->currentPageId = ORM::for_table('CrawledPage')->order_by_desc('id')->find_one()->id;

		}
		catch(Exception $ex) {

			logException($ex);
		}
	}


	function isRelativeLink($url) {

		return !strstr($url, "http");
	}


	function makeAbsoluteLink($url) {

		return $this->currentUrl . $url;
	}


	function getLinkHash($url) {

		$liteURL = substr($url, strpos($url, "//") + 2);
		if (strstr($liteURL, "index.php") || strstr($liteURL, "index.asp") ||
			strstr($liteURL, "index.htm"))
			$liteURL = substr($liteURL, 0, strrpos($liteURL, "//"));
		return md5($liteURL);
	}


	function getDomain($url) {

		return $this->urlResouce['host'];
	}

	//returneaza codul HTTP
	function httpResponse() {

		return $this->info['http_code'];
	}

	//returneaza urmatorul URL ne crawl-at din baza de date sau null daca nu exista
    function getNextLink() {


    	if (!isset($this->justStarted)) {
    		$this->justStarted = true;
    		return $this->currentUrl;
    	}


    	$nextLink = null;
    	try{
	    	//$nextLink = (string)ORM::for_table('Link')->raw_query("Select concat(domain,canonicalUrl) as concat_link from Link where concat(domain,canonicalUrl) not in (Select url from CrawledPage);")->find_one()->concat_link;
	    	$nextLink = (string)ORM::for_table('Link')->raw_query("Select canonicalUrl from Link where canonicalUrl not in (Select url from CrawledPage);")->find_one()->canonicalUrl;
	    	
	    	return $nextLink;
	    }
	    catch(Exception $ex) {

	    	logException($ex);
	    }

	    return $nextLink;
    }
    //returneaza tipul continutului paginii
    function getUrlMimeType($buffer) {

	    $finfo = new finfo(FILEINFO_MIME_TYPE);
	    return $finfo->buffer($buffer);
	}
	//verifica daca continutul paginii e html, nu alt fisier
	function isHtml($buffer) {

		crawlerLog("PAGE TYPE=".$this->getUrlMimeType($buffer));

		return strstr($this->getUrlMimeType($buffer), 'html');
	}

	//elibereaza memoria ale carei referinte s-au pierdut
	function manageMemory() {

			crawlerLog('MEM USAGE BEFORE GC - ' . memory_get_usage());
			gc_enable(); // Enable Garbage Collector
			crawlerLog(gc_collect_cycles() . " garbage cycles cleaned"); // # of elements cleaned up
			gc_disable(); // Disable Garbage Collector
			crawlerLog('MEM USAGE After GC - ' . memory_get_usage());
	}
	//seteaza locatia unde vor fi salvate fisierele html raw si clean text
	function setStorePageParams() {

		$this->currentTimestamp = date("Y-m-d H:i:s");
		$this->rawPagePath = pref_getSectionPreference('crawler', 'raw_page_path') . $this->currentTimestamp;
		$this->parsedTextPath = pref_getSectionPreference('crawler', 'parsed_page_path') . $this->currentTimestamp;
	}

	//verifica daca pagina poate fi descarcata si daca e HTML
	function pageOk() {

		crawlerLog("HTTP CODE " .$this->httpResponse());
		//verifica codul HTTP
		if ($this->httpResponse() >= 400) {
				crawlerLog("HTTP Error, URL Skipped");
				return false;
		}
		//verifica daca pagina e HTML
		if (!$this->isHtml($this->pageContent)) {

				crawlerLog("Page not HTML, URL Skipped");
				return false;
		}

		return true;
	}
	
	/*
	 * Salveaza pagina in format raw si clean text in fisiere 
	 */
	function saveCurrentPage() {


		try {
			//salveaza pagina raw pe disk
			file_put_contents($this->rawPagePath, $this->pageContent);
			//converteste simbolurile HTML in format text si elimina din spatii.
			$this->plainText = preg_replace("/  /", "", html_entity_decode($this->plainText));
			//salveaza textul extras pe disk
			file_put_contents($this->parsedTextPath, $this->plainText);
		}
		catch(Exception $ex) {

			logException($ex);
		}
	}

	//Clasele care deriva aceasta clasa vor trebui
	//sa implementeze metodele de mai jos

	abstract function extractText($domNode, $i);

	abstract function startCrawling($startUrl);
}


?>
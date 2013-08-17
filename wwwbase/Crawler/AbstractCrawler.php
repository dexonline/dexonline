<?php
/*
 * Alin Ungureanu, 2013
 * alyn.cti@gmail.com
 */
require_once '../../phplib/util.php';
require_once '../../phplib/serverPreferences.php';
require_once '../../phplib/db.php';
require_once '../../phplib/idiorm/idiorm.php';
require_once '../../phplib/idiorm/paris.php';

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
	protected $directoryIndexFile;
	protected $IndexFileExt;

	private $justStarted;


	function __construct() {

		$this->plainText = '';
		$this->pageContent = '';
		$this->directoryIndexFile = pref_getSectionPreference('crawler', 'dir_index_file');
		$this->IndexFileExt = explode(',', pref_getSectionPreference('crawler', 'index_file_ext'));
	}


	//descarca pagina de la $url
	function getPage($url) {

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
		$this->rawPagePath = pref_getSectionPreference('crawler', 'raw_page_path')
			.$this->urlResource['host'] .'/'. $this->currentTimestamp;
		$this->parsedTextPath = pref_getSectionPreference('crawler', 'parsed_text_path')
			.$this->urlResource['host'] .'/'. $this->currentTimestamp;
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
			if (!file_exists(pref_getSectionPreference('crawler','raw_page_path').$this->urlResource['host'])) {
				mkdir(pref_getSectionPreference('crawler','raw_page_path').$this->urlResource['host'], 0777, true);
			}
			if (!file_exists(pref_getSectionPreference('crawler','parsed_text_path').$this->urlResource['host'])) {
				mkdir(pref_getSectionPreference('crawler','parsed_text_path').$this->urlResource['host'], 0777, true);
			}
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



	//sterge directory index file si elimina slash-urile in plus
	function urlPadding($url) {

		return $this->delDuplicateSlashes($this->delDirIndexFile($url));
	}


	//delestes index.php/html/pl/py/jsp  etc
	function delDirIndexFile($url) {

		//crawlerLog('delDirIndexFile  '.$url);

		foreach($this->IndexFileExt as $ext) {

			$target = $this->directoryIndexFile .'.'. $ext;

			if (strstr($url, $target))
				return str_replace($target, "", $url);
		}

		return $url;
	}

	//deletes slashes when not needed
	function delDuplicateSlashes($url) {

		if (strlen($url) < 5) {

			crawlerLog("whatup with delDuplicateSlashes: $url");
			return $this->currentUrl;
		}
		

		$parsedUrl = parse_url($url);
		
		/*if (substr($parsedUrl['host'], 0, 4) != 'www.') {
		
			$parsedUrl['host'] = 'www.'.$parsedUrl['host'];
		}*/
		if (substr_count($parsedUrl['host'], '.') < 2) {

			$parsedUrl['host'] = 'www.'.$parsedUrl['host'];
		}

		$retUrl = $parsedUrl['scheme'].'://'.$parsedUrl['host'];
		$consecutiveSlash = false;

		$url = substr($url, strlen($retUrl));

		for ($i = 0; $i < strlen($url); ++$i) {
			$nextCh = substr($url, $i, 1);

			if ($nextCh == '/' && !$consecutiveSlash) {

				$retUrl .= $nextCh;
				$consecutiveSlash = true;
			}
			else if ($nextCh == '/') {}
			else {
				$retUrl .= $nextCh;
				$consecutiveSlash = false;
			}
		}

		//eliminarea slash-ului final
		//$retUrl = substr($retUrl, 0, -1);
		if (substr($retUrl, -1) == "/") $retUrl = substr($retUrl, 0, -1);

		//crawlerLog("DelDuplicateSlashes ". $retUrl);

		return $retUrl;
	}


	//gaseste toate linkurile
	//le transforma in absolute daca sunt relative
	function processLink($url) {

		crawlerLog('Processing link: '.$url);
		$canonicalUrl = null;
		if ($this->isRelativeLink($url)) {

			$url = $this->makeAbsoluteLink($url);
		}
		//daca ultimul caracter este '/', il eliminam
		//exemplu wiki.dexonline.ro nu wiki.dexonline.ro/
		if (substr($url, -1) == "/") $url = substr($url, 0, -1);

		//sterge slash-uri in plus si directory index file
		$canonicalUrl = $this->urlPadding($url);
		
		//$this->urlResource = parse_url($url);



		if (!strstr($url, $this->currentLocation)) return;


		$urlHash = $this->getLinkHash($url);

		$domain = $this->getDomain($url);

		Link::saveLink2DB($canonicalUrl, $domain, $urlHash, $this->currentPageId);
	}

	function isRelativeLink($url) {

		return !strstr($url, "http");
	}


	function getDeepestDir($url) {

		try {
			$retVal = substr($url, 0, strrpos($url,'/'));
			//crawlerLog("GetDeepestDir: " . $retVal);
			if (strstr($retVal, $this->currentLocation))
				return $retVal;
			else return $url;
		}
		catch(Exception $ex) {

			exceptionLog($ex);
		}
		return $url;
	}

	function makeAbsoluteLink($url) {

		return $this->getDeepestDir($this->currentUrl) .'/'. $url;
	}


	function getLinkHash($url) {

		$liteURL = substr($url, strpos($url, "//") + 2);
		if (strstr($liteURL, "index.php") || strstr($liteURL, "index.asp") ||
			strstr($liteURL, "index.htm"))
			$liteURL = substr($liteURL, 0, strrpos($liteURL, "//"));
		return md5($liteURL);
	}


	function getDomain($url) {

		return $this->urlResource['host'];
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


    	//$nextLink = null;
    	try {
	    	//$nextLink = (string)ORM::for_table('Link')->raw_query("Select concat(domain,canonicalUrl) as concat_link from Link where concat(domain,canonicalUrl) not in (Select url from CrawledPage);")->find_one()->concat_link;
	    	$nextLink = ORM::for_table('Link')->raw_query("Select canonicalUrl from Link where canonicalUrl not in (Select url from CrawledPage);")->find_one();
	    	
	    	if ($nextLink != null) {
	    	
	    		return $nextLink->canonicalUrl;
	    	}
	    }
	    catch(Exception $ex) {

	    	logException($ex);
	    }

	    return null;
    }

	//Clasele care deriva aceasta clasa vor trebui
	//sa implementeze metodele de mai jos

	abstract function extractText($domNode);

	abstract function startCrawling($startUrl);
}


?>
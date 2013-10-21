<?php
/*
 * Alin Ungureanu, 2013
 * alyn.cti@gmail.com
 */
require_once __DIR__ . '/../phplib/util.php';
require_once util_getRootPath() . 'phplib/simple_html_dom.php';

require_once util_getRootPath() . 'phplib/AppLog.php';
require_once util_getRootPath() . 'phplib/MemoryManagement.php';


db_init();

abstract class AbstractCrawler {

	protected $ch;
	protected $pageContent;
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
	protected $indexFileExt;

	protected $domainsList;


	function __construct() {

		$this->plainText = '';
		$this->pageContent = '';
		$this->directoryIndexFile = Config::get('crawler.dir_index_file');
		$this->indexFileExt = explode(',', Config::get('crawler.index_file_ext'));
		$this->fileExt = explode(',', Config::get('crawler.index_file_ext').',txt');
	}


	//descarca pagina de la $url
	function getPage($url) {

		$this->ch = curl_init();
		Applog::log("User agent is: " . Config::get('crawler.user_agent'));
		curl_setopt ($this->ch, CURLOPT_URL, $url);
		curl_setopt ($this->ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt ($this->ch, CURLOPT_USERAGENT, Config::get('crawler.user_agent'));
		curl_setopt ($this->ch, CURLOPT_TIMEOUT, 20);
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

		Applog::log("PAGE TYPE=".$this->getUrlMimeType($buffer));

		return strstr($this->getUrlMimeType($buffer), 'html');
	}

	
	//seteaza locatia unde vor fi salvate fisierele html raw si clean text
	function setStorePageParams() {

		$this->currentTimestamp = date("Y-m-d H:i:s");
		$this->rawPagePath = Config::get('crawler.raw_page_path')
			.$this->urlResource['host'] .'/'. $this->currentTimestamp;
		$this->parsedTextPath = Config::get('crawler.parsed_text_path')
			.$this->urlResource['host'] .'/'. $this->currentTimestamp;
	}

	//verifica daca pagina poate fi descarcata si daca e HTML
	function pageOk() {

		Applog::log("HTTP CODE " .$this->httpResponse());
		//verifica codul HTTP
		if ($this->httpResponse() >= 400) {
				Applog::log("HTTP Error, URL Skipped");
				return false;
		}
		//verifica daca pagina e HTML
		if (!$this->isHtml($this->pageContent)) {

				Applog::log("Page not HTML, URL Skipped");
				return false;
		}

		return true;
	}
	
	/*
	 * Salveaza pagina in format raw si clean text in fisiere 
	 */
	function saveCurrentPage() {


		try {
			if (!file_exists(Config::get('crawler.raw_page_path').$this->urlResource['host'])) {
				mkdir(Config::get('crawler.raw_page_path').$this->urlResource['host'], 0777, true);
			}
			if (!file_exists(Config::get('crawler.parsed_text_path').$this->urlResource['host'])) {
				mkdir(Config::get('crawler.parsed_text_path').$this->urlResource['host'], 0777, true);
			}
			//salveaza pagina raw pe disk
			file_put_contents($this->rawPagePath, $this->pageContent);
			//converteste simbolurile HTML in format text si elimina din spatii.
			$this->plainText = preg_replace("/  /", "", html_entity_decode($this->plainText));
			//salveaza textul extras pe disk
			file_put_contents($this->parsedTextPath, $this->plainText);
		}
		catch(Exception $ex) {

			Applog::exceptionLog($ex);
		}
	}

	//returneaza codul HTTP
	function httpResponse() {

		return $this->info['http_code'];
	}

	//returneaza urmatorul URL ne crawl-at din baza de date sau null daca nu exista
    function getNextLink() {


    	//$nextLink = null;
    	try {
	    	//$nextLink = (string)ORM::for_table('Link')->raw_query("Select concat(domain,canonicalUrl) as concat_link from Link where concat(domain,canonicalUrl) not in (Select url from CrawledPage);")->find_one()->concat_link;
	    	$nextLink = ORM::for_table('Link')->raw_query("Select canonicalUrl from Link where canonicalUrl LIKE '$this->currentLocation%' and canonicalUrl not in (Select url from CrawledPage);")->find_one();
	    	
	    	if ($nextLink != null) {
	    	
	    		return $nextLink->canonicalUrl;
	    	}
	    }
	    catch(Exception $ex) {

	    	Applog::exceptionLog($ex);
	    }

	    return null;
    }

    //repara HTML-ul stricat intr-un mod minimal astfel incat
    //sa poata fi interpretat de biblioteca simple_html_dom
    function fixHtml($html) {

    	foreach($html->find('head') as $script) {

			$script->outertext = '';
		}

    	foreach($html->find('script') as $script) {

			$script->outertext = '';
		}

		foreach($html->find('style') as $style) {

			$style->outertext = '';
		}

		$html->load($html->save());
		
		//transforma pagina raw in simple_html_dom_node
		//$this->dom = str_get_html($pageContent);
		
		$buffer = '<html><body>';
		$nodes = $html->childNodes();
		foreach($nodes as $node) {

			$buffer .= $node->innertext();
		}

		$buffer .= '</body></html>';

		return str_get_html($buffer);
    }

    function eligibleUrl($url) {

    	$resource = parse_utf8_url($url);
    	$pathInfo = pathinfo($resource['path']);

    	if (isset($pathInfo['extension'])) {

    		$ext = $pathInfo['extension'];


    		if (array_search($ext, $this->fileExt) === false) {

    			return false;
    		}
    	}

    	return true;
    }

    //metode pentru prelucrarea linkurilor
	//sterge directory index file si elimina slash-urile in plus
	//gaseste toate linkurile
	//le transforma in absolute daca sunt relative
	function processLink($url) {


		if (!$this->eligibleUrl($url)) {

			return;
		}

		Applog::log('Processing link: '.$url);
		$canonicalUrl = null;
		if ($this->isRelativeLink($url)) {

			$url = $this->makeAbsoluteLink($url);
		}
		//daca ultimul caracter este '/', il eliminam
		//exemplu wiki.dexonline.ro nu wiki.dexonline.ro/
		if (substr($url, -1) == "/") $url = substr($url, 0, -1);

		//sterge slash-uri in plus si directory index file
		$canonicalUrl = $this->urlPadding($url);
		
		if (!strstr($url, $this->currentLocation)) return;		

		Link::saveLink2DB($canonicalUrl, $this->getDomain($url), $this->currentPageId);
	}


	function urlPadding($url) {

		return $this->delDuplicateSlashes($this->delDirIndexFile($url));
	}


	//delestes index.php/html/pl/py/jsp  etc
	function delDirIndexFile($url) {

		//Applog::log('delDirIndexFile  '.$url);

		foreach($this->indexFileExt as $ext) {

			$target = $this->directoryIndexFile .'.'. $ext;

			if (strstr($url, $target))
				return str_replace($target, "", $url);
		}

		return $url;
	}

	//deletes slashes when not needed
	function delDuplicateSlashes($url) {

		if (strlen($url) < 5) {

			Applog::log("whatup with delDuplicateSlashes: $url");
			return $this->currentUrl;
		}
		

		$parsedUrl = parse_utf8_url($url);
		

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
	
		if (substr($retUrl, -1) == "/") $retUrl = substr($retUrl, 0, -1);

		return $retUrl;
	}


	function isRelativeLink($url) {

		return !strstr($url, "http");
	}

	//cauta directorul link-ului curent si returneaza
	//url-ul spre acel director
	function getDeepestDir($url) {

		try {
			$retVal = substr($url, 0, strrpos($url,'/'));

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

	function getDomain($url) {

		return $this->urlResource['host'];
	}


	//Clasele care deriva aceasta clasa vor trebui
	//sa implementeze metodele de mai jos
	abstract function extractText($domNode);

	abstract function crawlDomain();

	abstract function start();
}

?>1
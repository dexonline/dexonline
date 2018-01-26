<?php
/*
 * Alin Ungureanu, 2013
 * alyn.cti@gmail.com
 */
require_once __DIR__ . '/../phplib/Core.php';
require_once Core::getRootPath() . 'phplib/third-party/simple_html_dom.php';
require_once Core::getRootPath() . 'phplib/AppLog.php';
require_once Core::getRootPath() . 'phplib/MemoryManagement.php';

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
  protected $urlResource;
  protected $directoryIndexFile;
  protected $indexFileExt;
  protected $accessTimes;

  function __construct() {
    $this->plainText = '';
    $this->pageContent = '';
    $this->directoryIndexFile = Config::get('crawler.dir_index_file');
    $this->indexFileExt = explode(',', Config::get('crawler.index_file_ext'));
    $this->fileExt = explode(',', Config::get('crawler.index_file_ext').',txt');
    
    $this->accessTimes = [];
    foreach (Config::get('crawler.whiteList') as $startUrl) {
      $rec = Str::parseUtf8Url($startUrl);
      $this->accessTimes[$rec['host']] = 0;
    }
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
    } else{
      $this->info = ['http_code' => 404];
    }

    curl_close($this->ch);

    // Update access time for this page's host
    $rec = Str::parseUtf8Url($url);
    $this->accessTimes[$rec['host']] = time();

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
    $date = date("Y-m-d H:i:s");
    $this->currentTimestamp = time();
    $this->rawPagePath = Config::get('crawler.raw_page_path') . $this->urlResource['host'] . '/' . $date;
    $this->parsedTextPath = Config::get('crawler.parsed_text_path') . $this->urlResource['host'] . '/' . $date;
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
  
  //returneaza codul HTTP
  function httpResponse() {

    return $this->info['http_code'];
  }

  // Returneaza urmatorul URL ne crawl-at din baza de date.
  // Returneaza null dacă niciun URL nu poate fi crawlat încă.
  function getNextLink() {
    $delay = Config::get('crawler.t_wait');
    foreach (Config::get('crawler.whiteList') as $startUrl) {
      $rec = Str::parseUtf8Url($startUrl);
      if ($this->accessTimes[$rec['host']] < time() - $delay) {
        $query = sprintf("select canonicalUrl from Link where domain = '%s' and canonicalUrl not in (select url from CrawledPage)", $rec['host']);
        $link = Model::factory('Link')->raw_query($query)->find_one();
        if ($link) {
          return $link;
        }
      }
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
    $resource = Str::parseUtf8Url($url);
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
    Applog::log('Processing link: '.$url);
    $canonicalUrl = null;
    if ($this->isRelativeLink($url)) {
      $url = $this->makeAbsoluteLink($url);
    }

    //sterge slash-uri in plus si directory index file
    $canonicalUrl = Str::urlCleanup($url, $this->directoryIndexFile, $this->indexFileExt);

    if (!$this->eligibleUrl($url)) {
      return;
    }

    $rec = Str::parseUtf8Url($canonicalUrl);
    if ($rec['host'] == $this->getDomain($url)) {
      Link::saveLink2DB($canonicalUrl, $this->getDomain($url), $this->currentPageId);
    }
  }

  function isRelativeLink($url) {
    return !strstr($url, "http");
  }

  // Cauta directorul link-ului curent si returneaza url-ul spre acel director
  // Returnează întregul URL dacă nu există un director.
  function getDeepestDir($url) {
    $parts = explode('//', $url, 2); // Salvează protocolul
    $pos = strrpos($parts[1], '/');
    if ($pos !== false) {
      $parts[1] = substr($parts[1], 0, $pos);
    }
		return implode('//', $parts);    
  }

  function makeAbsoluteLink($url) {
    return $this->getDeepestDir($this->currentUrl) . '/' . $url;
  }

  function getDomain($url) {

    return $this->urlResource['host'];
  }


  // Clasele care deriva aceasta clasa vor trebui sa implementeze metodele de mai jos
  abstract function extractText($domNode);
  abstract function start();
}

<?php
/*
 * Alin Ungureanu, 2013
 * alyn.cti@gmail.com
 */
require_once __DIR__ . '/AbstractCrawler.php';

class Crawler extends AbstractCrawler {

  //extrage textul cu cod html din nodul respectiv
  function extractText($domNode) {
    Applog::log("extracting text");
    $this->plainText = html_entity_decode(strip_tags($domNode->text()));
    $this->plainText = preg_replace("/  +/", " ", $this->plainText);
  }

  /* Returns an array of links */
  function processPage($pageContent) {
    try {
      $links = array();
      $html = str_get_html($pageContent);

      //reparam html stricat
      if (!$html->find('body', 0, true)) {

        $html = $this->fixHtml($html);
      }
      

      $body = $html->find('body', 0, true);
      $this->extractText($body);
      foreach ($body->find("a") as $link) {
        $links[] = $link->href;
      }
      //cata memorie consuma
      //si eliberare referinte pierdute
      
      $html->clear();

      MemoryManagement::showUsage('before cleaning', true, 'KB');
      
      MemoryManagement::clean(true);

      MemoryManagement::showUsage('after cleaning', true, 'KB');
      return $links;
    }
    catch (Exception $ex) {

      Applog::exceptionLog($ex);
    }
  }

  function crawlDomain() {

    Applog::log("Crawling: " . $this->getDomain($this->currentUrl) . " started");

    while (1) {

      //extrage urmatorul link neprelucrat din baza de date
      $url = $this->getNextLink();
      Applog::log('current URL: ' . $url);
      //daca s-a terminat crawling-ul
      if ($url == null || $url == '') break;

      //download pagina
      $pageContent = $this->getPage($url);
      //setam url-ul curent pentru store in Database
      $this->currentUrl = $url;
      $links = $this->processPage($pageContent);

      $this->setStorePageParams();

      //salveaza o intrare despre pagina curenta in baza de date
      $this->currentPageId = CrawledPage::savePage2DB($this->currentUrl, $this->httpResponse(), $this->pageContent, $this->plainText, $this->rawPagePath, $this->parsedTextPath, $this->currentTimestamp);

      //daca pagina nu e in format html (e imagine sau alt fisier)
      //sau daca am primit un cod HTTP de eroare, sarim peste pagina acesta
      if (!$this->pageOk()) {
        continue;
      }
      
      foreach($links as $link) {
        $this->processLink($link);
      }

      //niceness
      sleep(Config::get('crawler.t_wait'));
    }

    Applog::log("Crawling: " . $this->getDomain($this->currentUrl) . " finished");
  }


  function start() {
  
    Applog::log("Crawler started");

    $this->domainsList = Config::get('crawler.whiteList');

    //start processing 
    $this->processWhiteList();

    Applog::log('Crawler finished');
  }


  function processWhiteList() {
    foreach($this->domainsList as $startUrl) {
      $startUrl = trim($startUrl);

      //curatam url-ul
      $this->currentUrl = StringUtil::urlCleanup($startUrl, $this->directoryIndexFile, $this->indexFileExt);
      //impartim url-ul pe componente
      $this->urlResource = util_parseUtf8Url($this->currentUrl);

      //salvam startUrl in tabelul Link pentru a incepe extragerea,
      //startUrl nu va avea o pagina din care este descoperit
      //asa ca pagina crawledPageId va avea valoarea 0.
      Link::saveLink2DB($this->currentUrl, $this->getDomain($this->currentUrl), '0');

      //locatia curenta, va fi folosita pentru a nu depasi sfera
      //de exemplu vrem sa crawlam doar o anumita zona a site-ului
      $this->currentLocation = substr($this->currentUrl, 0);
      Applog::log('domain start location: '.$this->currentLocation);

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

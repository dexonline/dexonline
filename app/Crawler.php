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

  function crawlLoop() {

    Applog::log("Crawling: " . $this->getDomain($this->currentUrl) . " started");

    while (1) {
      //extrage urmatorul link neprelucrat din baza de date
      $link = $this->getNextLink();
      if ($link) {
        Applog::log('current URL: ' . $link->canonicalUrl);

        //download pagina
        $pageContent = $this->getPage($link->canonicalUrl);
        //setam url-ul curent pentru store in Database
        $this->currentUrl = $link->canonicalUrl;
        $this->urlResource = Str::parseUtf8Url($link->canonicalUrl);
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
      }

      // Sleep until we're guaranteed to have something to crawl, but no less than 1 second.
      $sleepTime = 1 + max(0, min($this->accessTimes) + Config::get('crawler.t_wait') - time());
      Applog::log("Sleeping for $sleepTime seconds");
      sleep($sleepTime);
    }
  }


  function start() {
    Applog::log("Crawler started");

    // Salvam întregul whiteList in tabelul Link pentru a incepe extragerea.
    // Aceste URL-uri nu vor avea o pagina din care sunt descoperite, deci crawledPageId va avea valoarea 0.
    foreach (Config::get('crawler.whiteList') as $startUrl) {
      $startUrl = Str::urlCleanup($startUrl, $this->directoryIndexFile, $this->indexFileExt);
      $rec = Str::parseUtf8Url($startUrl);
      Link::saveLink2DB($startUrl, $rec['host'], 0);
    }

    $this->crawlLoop();
  }

}

/*
 *  Obiectul nu va fi creat daca acest fisier nu va fi fisier cautat
 */
if (strstr( $_SERVER['SCRIPT_NAME'], 'Crawler.php')) {

  $obj = new Crawler();

  $obj->start();
}

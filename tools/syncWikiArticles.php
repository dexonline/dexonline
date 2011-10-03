<?

require_once("../phplib/util.php");
log_scriptLog('Running syncWikiArticles.php');

define('CATEGORY_LISTING_URL', 'http://lingv.dexonline.ro/api.php?action=query&list=categorymembers&cmtitle=Categorie:Sincronizare&cmlimit=max&cmsort=timestamp&cmdir=desc&format=xml');
define('PAGE_LISTING_URL', 'http://lingv.dexonline.ro/api.php?action=query&pageids=%s&prop=info&inprop=url&format=xml');
define('PAGE_RENDER_URL', 'http://lingv.dexonline.ro/index.php?action=render&curid=%d');
define('PAGE_RAW_URL', 'http://lingv.dexonline.ro/index.php?action=raw&curid=%d');

// Get the most recently edited category members
$xml = simplexml_load_file(CATEGORY_LISTING_URL);
if ($xml === false) {
  log_scriptLog('Cannot get category listing from ' . CATEGORY_LISTING_URL);
  exit(1);
}
$pageIds = array();
$pageIdHash = array();
foreach ($xml->query->categorymembers->cm as $cm) {
  $pageId = (string)$cm->attributes()->pageid;
  $pageIds[] = $pageId;
  $pageIdHash[$pageId] = true;
}

// Now get the latest revision for each page and, if it's newer than what we have (or if we don't have it at all), fetch it
$pageListingUrl = sprintf(PAGE_LISTING_URL, implode('|', $pageIds));
$xml = simplexml_load_file($pageListingUrl);
if ($xml === false) {
  log_scriptLog('Cannot get page info from ' . PAGE_LISTING_URL);
  exit(1);
}
foreach ($xml->query->pages->page as $page) {
  $pageId = (int)$page->attributes()->pageid;
  $title = (string)$page->attributes()->title;
  $lastRevId = (int)$page->attributes()->lastrevid;
  $fullUrl = (string)$page->attributes()->fullurl;

  $curPage = WikiArticle::get("pageId = $pageId");
  if (!$curPage || $curPage->revId < $lastRevId) {
    $pageRenderUrl = sprintf(PAGE_RENDER_URL, $pageId);
    $pageRawUrl = sprintf(PAGE_RAW_URL, $pageId);

    if (!$curPage) {
      $curPage = new WikiArticle();
      $curPage->pageId = $pageId;
    }
    $curPage->revId = $lastRevId;
    $curPage->title = $title;
    $curPage->fullUrl = $fullUrl;
    $curPage->wikiContents = file_get_contents($pageRawUrl);
    if ($curPage->wikiContents === false) {
      log_scriptLog("Cannot fetch raw page from $pageRawUrl");
      exit(1);
    }
    $curPage->htmlContents = file_get_contents($pageRenderUrl);
    if ($curPage->htmlContents === false) {
      log_scriptLog("Cannot fetch rendered page from $pageRenderUrl");
      exit(1);
    }
    $curPage->save();
    WikiKeyword::deleteByWikiArticleId($curPage->id);
    $keywords = $curPage->extractKeywords();
    foreach ($keywords as $keyword) {
      $wk = new WikiKeyword();
      $wk->wikiArticleId = $curPage->id;
      $wk->keyword = $keyword;
      $wk->save();
    }
    log_scriptLog("Saved page #{$pageId} \"{$title}\"");
  }
}

// Now delete all the pages on our side that aren't category members because
//   (a) they have been deleted or
//   (b) they have been removed from the category
$ourIds = db_getArray(db_execute('select pageId from WikiArticle'));
foreach ($ourIds as $ourId) {
  if (!array_key_exists($ourId, $pageIdHash)) {
    $curPage = WikiArticle::get("pageId = $ourId");
    log_scriptLog("Deleting page #{$curPage->pageId} \"{$curPage->title}\"");
    $curPage->delete();
  }
}

log_scriptLog('syncWikiArticles.php finished');

?>

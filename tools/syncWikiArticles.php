<?php

require_once __DIR__ . "/../phplib/util.php";
log_scriptLog('Running syncWikiArticles.php');

define('CATEGORY_LISTING_URL', 'http://wiki.dexonline.ro/api.php?action=query&list=categorymembers&cmtitle=Categorie:Sincronizare&cmlimit=max&cmsort=timestamp&cmdir=desc&format=xml');
define('PAGE_LISTING_URL', 'http://wiki.dexonline.ro/api.php?action=query&pageids=%s&prop=info&inprop=url&format=xml');
define('PARSER_URL', 'http://wiki.dexonline.ro/api.php');
define('PAGE_RAW_URL', 'http://wiki.dexonline.ro/index.php?action=raw&curid=%d');

$options = getopt('', array('force'));
$force = array_key_exists('force', $options);

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

  $curPage = WikiArticle::get_by_pageId($pageId);
  if (!$curPage || $curPage->revId < $lastRevId || $force) {
    $pageRawUrl = sprintf(PAGE_RAW_URL, $pageId);

    if (!$curPage) {
      $curPage = Model::factory('WikiArticle')->create();
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
    $curPage->htmlContents = parse($curPage->wikiContents);
    if ($curPage->htmlContents === false) {
      log_scriptLog("Cannot parse page");
      exit(1);
    }
    $curPage->save();

    WikiKeyword::deleteByWikiArticleId($curPage->id);
    $keywords = $curPage->extractKeywords();
    foreach ($keywords as $keyword) {
      $wk = Model::factory('WikiKeyword')->create();
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
$ourIds = db_getArray('select pageId from WikiArticle');
foreach ($ourIds as $ourId) {
  if (!array_key_exists($ourId, $pageIdHash)) {
    $curPage = WikiArticle::get_by_pageId($ourId);
    log_scriptLog("Deleting page #{$curPage->pageId} \"{$curPage->title}\"");
    $curPage->delete();
  }
}

log_scriptLog('syncWikiArticles.php finished');

/*************************************************************************/

function parse($text) {
  // Preprocessing
  $text = "__NOEDITSECTION__\n" . $text; // Otherwise the returned HTML will contain section edit links
  $text = str_replace(array('ş', 'Ş', 'ţ', 'Ţ'), array('ș', 'Ș', 'ț', 'Ț'), $text);

  // Actual parsing
  $xmlString = util_makePostRequest(PARSER_URL, array('action' => 'parse', 'text' => $text, 'format' => 'xml', 'editsection' => false));
  $xml = simplexml_load_string($xmlString);
  $html = (string)$xml->parse->text;
  if (!$html) {
    return false;
  }

  // Postprocessing
  // Convert links to other articles, even if they are not under [[Categorie:Sincronizare]]
  $html = str_replace('href="/wiki/', 'href="/articol/', $html);

  // Fully qualify links to index.php. Most likely, these are link to non-existant articles.
  $html = str_replace('href="/index.php', 'href="http://wiki.dexonline.ro/index.php', $html);

  return $html;
}

?>

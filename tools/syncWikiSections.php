<?

define('DEBUG', 0);
if (DEBUG) echo "START\n";

require_once("../phplib/util.php");
log_scriptLog('Running syncWikiSections.php');

define('ARTICLE_SECTONS_URL', 'http://lingv.dexonline.ro/api.php?action=query&list=allcategories&acprefix=Articole&format=xml');
define('SECTION_PAGES_TMPL_URL', 'http://lingv.dexonline.ro/api.php?action=query&list=categorymembers&cmtitle=Categorie:%s&cmlimit=max&format=xml&cmsort=timestamp&cmdir=desc');

//Preluăm toate secțiunile prefixate cu Articole:
$xml = simplexml_load_file(ARTICLE_SECTONS_URL);
if ($xml === false) {
  log_scriptLog('Cannot get sections listing from ' . ARTICLE_SECTONS_URL);
  if (DEBUG) echo "Eroare la citirea secțiunilor\n";
  exit(1);
}

WikiSection::truncate();
log_scriptLog('Sections cleaned (truncate table)');
if (DEBUG) echo "Am resetat tabela...";

foreach ($xml->query->allcategories->c as $section) {
    log_scriptLog('Diving into ' + $section);
    if (DEBUG) echo "Citim secțiunea " . $section . "\n";

    $url = sprintf(SECTION_PAGES_TMPL_URL, $section);
    $xmlpages = simplexml_load_file($url);
    if ($xmlpages === false) {
      log_scriptLog('Cannot get sections pages from ' . $url);
      if (DEBUG) echo "Eroare la citirea paginilor din secțiuni\n";
      exit(1);
    }
    foreach ($xmlpages->query->categorymembers->cm as $cm) {
      $pageId = (string)$cm->attributes()->pageid;
      $pageTitle = (string)$cm->attributes()->title;

      $ws = Model::factory('WikiSection')->create();
      $ws->pageId = $pageId;
      $ws->section = str_replace('Articole:', '', $section);
      $ws->save();
      if (DEBUG) echo "{$pageTitle} ({$pageId}) ---> {$section}\n";
      log_scriptLog("Page {$pageTitle} ({$pageId}) saved in section {$section}");
    }

}


log_scriptLog('syncWikiArticles.php finished');

?>

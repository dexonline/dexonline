<?php
require_once __DIR__ . '/../phplib/Core.php';

// TODO: Add user pages
// TODO: Add inflection models (from modele-flexiune.php)

define('FILE_SIZE_LIMIT', 9000000);
define('FILE_URL_LIMIT', 45000);
$g_numFiles = 0;
$g_curFileName = null;
$g_curFile = null;
$g_curFileSize = 0;
$g_curFileUrl = 0;

Log::notice('started');

chdir(Core::getRootPath());
openNewFile();
addOtherUrls();

$query = 'select id, formNoAccent from Lexem order by formNoAccent';
Log::info("Running mysql query: [$query]");
$dbResult = DB::execute($query);

$rowB = array(null, null);
$rowC = array(null, null);
foreach ($dbResult as $dbRow) {
  // Keep a moving window of 3 lexems that we can use to eliminate duplicates
  $rowA = $rowB;
  $rowB = $rowC;
  $rowC = $dbRow;
  if ($rowB[1] && $rowB[1] != $rowA[1]) {
    // If 2 or more lexems have identical forms, only add a definition URL for the first one
    addUrl('https://dexonline.ro/definitie/' . urlencode($rowB[1]));
  }
  if ($rowB[0] && ($rowB[1] == $rowA[1] || $rowB[1] == $rowC[1])) {
    // Only add a link to the lexem if it has homonyms. Otherwise, its page is identical to the definition page.
    addUrl("https://dexonline.ro/lexem/{$rowB[1]}/{$rowB[0]}");
  }
}
// Now process the last row
if ($rowC[1] == $rowB[1]) {
  addUrl("https://dexonline.ro/lexem/{$rowC[1]}/{$rowC[0]}");
} else {
  addUrl('https://dexonline.ro/definitie/' . urlencode($rowC[1]));
}

closeCurrentFile();
generateIndexFile();
Log::notice('finished');

/*************************************************************************/

function addUrl($url) {
  global $g_curFile;
  global $g_curFileSize;
  global $g_curFileUrl;

  if ($g_curFileSize >= FILE_SIZE_LIMIT || $g_curFileUrl >= FILE_URL_LIMIT) {
    closeCurrentFile();
    openNewFile();
  }

  $output = "<url><loc>{$url}</loc></url>\n";
  $g_curFileSize += strlen($output);
  $g_curFileUrl++;
  fwrite($g_curFile, $output);
}

function addOtherUrls() {
  addUrl('https://dexonline.ro/');
  addUrl('https://dexonline.ro/contact');
  addUrl('https://dexonline.ro/contribuie');
  addUrl('https://dexonline.ro/articol/Ghid_de_exprimare_corect%C4%83');
  addUrl('https://dexonline.ro/licenta');
  addUrl('https://dexonline.ro/auth/login');
  addUrl('https://dexonline.ro/unelte');
  addUrl('https://dexonline.ro/top');
}

function closeCurrentFile() {
  global $g_numFiles;
  global $g_curFileName;
  global $g_curFile;

  fprintf($g_curFile, "</urlset>\n");
  fclose($g_curFile);
  OS::executeAndAssert("gzip - < {$g_curFileName} > wwwbase/sitemap{$g_numFiles}.xml.gz");
  OS::deleteFile($g_curFileName);
}

function openNewFile() {
  global $g_numFiles;
  global $g_curFileName;
  global $g_curFile;
  global $g_curFileSize;
  global $g_curFileUrl;

  $g_numFiles++;
  $g_curFileName = tempnam(Config::get('global.tempDir'), 'sitemap_');
  $g_curFile = fopen($g_curFileName, 'w');
  $g_curFileSize = 0;
  $g_curFileUrl = 0;

  fprintf($g_curFile, "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n");
  fprintf($g_curFile, "<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">\n");
  Log::info("Opening next temporary sitemap file {$g_curFileName}");
}

function generateIndexFile() {
  global $g_numFiles;

  Log::info("Writing sitemap index sitemap.xml");
  $f = fopen('wwwbase/sitemap.xml', 'w');
  fprintf($f, "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n");
  fprintf($f, "<sitemapindex xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">\n");

  for ($i = 1; $i <= $g_numFiles; $i++) {
    fprintf($f, "  <sitemap>\n");
    fprintf($f, "    <loc>https://dexonline.ro/sitemap{$i}.xml.gz</loc>\n");
    fprintf($f, "  </sitemap>\n");
  }

  fprintf($f, "</sitemapindex>\n");
  fclose($f);
}

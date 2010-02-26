<?
require_once('../phplib/util.php');

// TODO: Add user pages
// TODO: Add inflection models (from viewModels.php)

define('FILE_SIZE_LIMIT', 9000000);
define('FILE_URL_LIMIT', 45000);
$g_numFiles = 0;
$g_curFileName = null;
$g_curFile = null;
$g_curFileSize = 0;
$g_curFileUrl = 0;

log_scriptLog('Running generateSitemap.php');

openNewFile();
addOtherUrls();

$query = 'select * from lexems order by lexem_id';
log_scriptLog("Running mysql query: [$query]");
$dbResult = mysql_query($query);

// Generate the Sitemap files
$numLexems = mysql_num_rows($dbResult);
while ($dbRow = mysql_fetch_assoc($dbResult)) {
  $lexem = Lexem::createFromDbRow($dbRow);
  addUrl('http://dexonline.ro/definitie/' . urlencode($lexem->unaccented));
  addUrl("http://dexonline.ro/lexem/{$lexem->id}");
}
closeCurrentFile();
generateIndexFile();
log_scriptLog('generateSitemap.php completed');

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
  addUrl('http://dexonline.ro/');
  addUrl('http://dexonline.ro/faq.php');
  addUrl('http://dexonline.ro/contrib.php');
  addUrl('http://dexonline.ro/corect.php');
  addUrl('http://dexonline.ro/tools.php');
  addUrl('http://dexonline.ro/top.php');
  addUrl('http://dexonline.ro/contact.php');
  addUrl('http://dexonline.ro/login.php');
  addUrl('http://dexonline.ro/signup.php');
  addUrl('http://dexonline.ro/license.php');
  addUrl('http://dexonline.ro/codeAccess.php');
  addUrl('http://dexonline.ro/update.php');
  addUrl('http://dexonline.ro/update3.php');
}

function closeCurrentFile() {
  global $g_numFiles;
  global $g_curFileName;
  global $g_curFile;

  fprintf($g_curFile, "</urlset>\n");
  fclose($g_curFile);
  os_executeAndAssert("gzip - < {$g_curFileName} > ../wwwbase/sitemap{$g_numFiles}.xml.gz");
  util_deleteFile($g_curFileName);
}

function openNewFile() {
  global $g_numFiles;
  global $g_curFileName;
  global $g_curFile;
  global $g_curFileSize;
  global $g_curFileUrl;

  $g_numFiles++;
  $g_curFileName = tempnam('/tmp', 'sitemap_');
  $g_curFile = fopen($g_curFileName, 'w');
  $g_curFileSize = 0;
  $g_curFileUrl = 0;

  fprintf($g_curFile, "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n");
  fprintf($g_curFile, "<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">\n");
  log_scriptLog("Opening next temporary sitemap file {$g_curFileName}");
}

function generateIndexFile() {
  global $g_numFiles;

  log_scriptLog("Writing sitemap index sitemap.xml");
  $f = fopen('../wwwbase/sitemap.xml', 'w');
  fprintf($f, "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n");
  fprintf($f, "<sitemapindex xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">\n");

  for ($i = 1; $i <= $g_numFiles; $i++) {
    fprintf($f, "  <sitemap>\n");
    fprintf($f, "    <loc>http://dexonline.ro/sitemap{$i}.xml.gz</loc>\n");
    fprintf($f, "  </sitemap>\n");
  }

  fprintf($f, "</sitemapindex>\n");
  fclose($f);
}

?>

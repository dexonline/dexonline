<?
require_once('phplib/util.php');
require_once('phplib/ads/adsModule.php');
require_once('phplib/ads/diverta/divertaAdsModule.php');
define('IMG_URL_PREFIX', 'http://www.dol.ro/wcsstore/DOL/');
define('ORIG_FILE_PREFIX', '/tmp/dol/big/');
define('THUMB_PREFIX', util_getRootPath() . 'wwwbase/img/diverta/thumb/');
define('IMG_NORMAL', 0);
define('IMG_NOT_JPEG', 1);
define('IMG_CORRUPT', 2);

$opts = getopt(null, array('file:', 'header-rows:', 'delim:', 'sku:', 'title:', 'author:', 'publisher:', 'url:', 'image-url:'));
if (count($opts) != 9) {
  usage();
}
define('CSV_DELIMITER', getDelimiter($opts['delim']));
os_executeAndAssert('mkdir -p ' . ORIG_FILE_PREFIX);

$lines = file($opts['file']);
$numLines = count($lines);
foreach ($lines as $i => $line) {
  if ($i < $opts['header-rows']) {
    continue;
  }
  $fields = str_getcsv($line, CSV_DELIMITER);
  $sku = $fields[$opts['sku']];
  print "Line $i/$numLines: [$sku]\n";

  // Reuse the record or create a new one
  $book = DivertaBook::get("sku = '{$sku}'");
  if ($book) {
    print "  Reusing book id = {$book->id}\n";
  } else {
    print "  Creating new book\n";
    $book = new DivertaBook();
    $book->sku = $sku;
    $book->impressions = 0;
    $book->clicks = 0;
  }
  $book->title = $fields[$opts['title']];
  $book->author = $fields[$opts['author']];
  $book->publisher = $fields[$opts['publisher']];
  $book->imageUrl = IMG_URL_PREFIX . $fields[$opts['image-url']];
  $book->url = $fields[$opts['url']];
  $book->save();
  print "  [{$book->title}] by [{$book->author}]\n";

  associateLexems($book);

  // Grab the image unless we already have it
  $fileName = ORIG_FILE_PREFIX . "{$sku}.jpg";
  $thumbName = THUMB_PREFIX . "{$sku}.jpg";
  if (file_exists($fileName)) {
    $haveFile = true;
  } else {
    $haveFile = false;
    $img = util_fetchUrl($book->imageUrl);
    if ($img !== false) {
      // Dump the image to a file
      $file = fopen ($fileName, "w");
      fwrite($file, $img);
      fclose ($file);
      $haveFile = true;
    }
  }

  $alreadyResized = file_exists($thumbName);
  if ($haveFile && !$alreadyResized) {
    $imgType = isImage($fileName);
    if ($imgType == IMG_NORMAL) {
      list ($width, $height, $bytes) = preg_split('/\|/', getImageInfo($fileName));
      print "  {$width}x{$height}, {$bytes} bytes ";
      os_executeAndAssert("convert -trim -fuzz \"3%\" -geometry 200x84 \"$fileName\" \"$thumbName\"");
      if ($width <= 90 && $height <= 90) {
        print "*small* ";
      }
      list ($thumbWidth, $thumbHeight, $ignored) = preg_split('/\|/', getImageInfo($thumbName));
      $book->thumbWidth = $thumbWidth;
      $book->thumbHeight = $thumbHeight;
      $book->save();
    } else if ($imgType == IMG_NOT_JPEG) {
      print "  Not an image ";
    } else {
      print "  Corrupted image ";
    }
  } elseif ($alreadyResized) {
    print "  Already processed, skipping ";
  } else {
    print "  Cannot fetch image! ";
  }
  print "\n";
}

function getImageInfo($fileName) {
  $output = os_executeAndReturnOutput("identify -format \"%w|%h|%b\" \"$fileName\"");
  assert(count($output) == 1);
  return $output[0];
}

function isImage($fileName) {
  // Check that the image exists
  $output = os_executeAndReturnOutput("file \"$fileName\"");
  assert(count($output) == 1);
  if (strpos($output[0], 'JPEG image data') === false) {
    return IMG_NOT_JPEG;
  }

  $output = os_executeAndReturnOutput("identify -verbose \"$fileName\" 2>&1 1>/dev/null");
  if (count($output)) {
    return IMG_CORRUPT; // because there are warnings
  }
  return IMG_NORMAL;
}

function usage() {
  print "Required arguments:\n";
  print "--file         input file\n";
  print "--header-rows  number of header rows to discard\n";
  print "--delim        CSV delimiter (one of the strings 'tab', 'comma' or 'space'\n";
  print "--sku          column number for the sku (all column numbers are 0-based)\n";
  print "--title        column number for the title\n";
  print "--author       column number for the author\n";
  print "--publisher    column number for the publisher\n";
  print "--url          column number for the book URL\n";
  print "--image-url    column number for the image URL\n";
  exit(1);
}

function getDelimiter($string) {
  switch ($string) {
    case 'tab': return "\t";
    case 'comma': return ',';
    case 'space': return ' ';
    default: print "--delim can be one of the strings 'tab', 'comma' or 'space'\n"; exit(1);
  }
}

function associateLexems(&$book) {
  db_execute("delete from diverta_Index where bookId = {$book->id}");
  $hasDiacritics = text_hasDiacritics($book->title);
  $title = mb_strtolower($book->title);
  $title = str_replace(array(',', '.'), '', $title);
  $titleWords = preg_split("/\\s+/", $title);
  $lexemIds = array();

  foreach ($titleWords as $word) {
    if (!text_isStopWord($word, $hasDiacritics)) {
      $field = $hasDiacritics ? 'formNoAccent' : 'formUtf8General';
      $wordLexemIds = db_getArray(db_execute("select distinct lexemId from InflectedForm where $field = '" . addslashes($word) . "'"));
      foreach ($wordLexemIds as $lexemId) {
        $lexemIds[$lexemId] = true;
      }
    }
  }

  foreach ($lexemIds as $lexemId => $ignored) {
    $index = new DivertaIndex();
    $index->lexemId = $lexemId;
    $index->bookId = $book->id;
    $index->save();
  }
}

?>

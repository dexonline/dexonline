<?php
/**
 * This script (re)generates 3 scrabble lists:
 * (1) base forms with accent, model type, model number and restrictions;
 * (2) inflected forms with accents, without duplicates;
 * (3) reduced forms (no accents, diacritics or duplicates, between 2 and 15 characters).
 * It does this for all LOC versions or for the most recent one.
 * It also generates diffs between versions.
 **/

require_once __DIR__ . '/../phplib/Core.php';
ini_set('max_execution_time', '3600');
ini_set('memory_limit', '256M');

Log::notice('started');

define('STATIC_SERVER_DIR', '/download/scrabble');

$allVersions = array_key_exists('a', getopt('a'));
$f = new FtpUtil();

(new BaseFormList($allVersions, $f))->run();
(new InflectedFormList($allVersions, $f))->run();
(new ReducedFormList($allVersions, $f))->run();

Log::notice('finished');

/***************************************************************************/

abstract class FormList {
  var $allVersions;
  var $ftpUtil;
  var $staticServer;
  var $tmpDir;

  function __construct($allVersions, $ftpUtil) {
    $this->allVersions = $allVersions;
    $this->ftpUtil = $ftpUtil;
    $this->staticServer = Config::get('static.url');
    $this->tmpDir = Config::get('global.tempDir');
  }

  abstract function getName();
  abstract function getFilePattern();
  abstract function getDiffPattern();
  abstract function getQuery();
  abstract function format($row);
  abstract function postProcess($fileName);

  function getTxtFileName($version) {
    return sprintf($this->getFilePattern(), $this->tmpDir, $version->name, 'txt');
  }

  function getZipFileName($version) {
    return sprintf($this->getFilePattern(), $this->tmpDir, $version->name, 'zip');
  }

  function getFtpFileName($version) {
    return sprintf($this->getFilePattern(), STATIC_SERVER_DIR, $version->name, 'zip');
  }

  function getUrl($version) {
    return sprintf($this->getFilePattern(), $this->staticServer . STATIC_SERVER_DIR, $version->name, 'zip');
  }

  function getTxtDiffFileName($v1, $v2) {
    return sprintf($this->getDiffPattern(), $this->tmpDir, $v1->name, $v2->name, 'txt');
  }

  function getZipDiffFileName($v1, $v2) {
    return sprintf($this->getDiffPattern(), $this->tmpDir, $v1->name, $v2->name, 'zip');
  }

  function getFtpDiffFileName($v1, $v2) {
    return sprintf($this->getDiffPattern(), STATIC_SERVER_DIR, $v1->name, $v2->name, 'zip');
  }

  function writeForms($fileName) {
    $dbResult = DB::execute($this->getQuery());
    $f = fopen($fileName, 'w');
    foreach ($dbResult as $r) {
      fprintf($f, $this->format($r) . "\r\n");
    }
    fclose($f);
  }

  // Compresses the txt file and uploads it to the static server
  function zipAndShip($txtFile, $zipFile, $ftpFile) {
    Log::info('* zipping');
    OS::executeAndAssert("zip -j {$zipFile} {$txtFile}");

    Log::info('* copying over FTP');
    $this->ftpUtil->staticServerPut($zipFile, $ftpFile);
  }

  function downloadAndUnzip($version) {
    $url = $this->getUrl($version);
    $zipFile = $this->getZipFileName($version);
    $txtFile = $this->getTxtFileName($version);
    if (!file_exists($txtFile)) {
      // Download only if necessary. We may still have this file from before.
      file_put_contents($zipFile, file_get_contents($url));
      OS::executeAndAssert("unzip -p $zipFile > $txtFile");
    }
  }

  function cleanup() {
    foreach (Config::getLocVersions() as $v) {
      @unlink($this->getTxtFileName($v));
      @unlink($this->getZipFileName($v));
    }

    foreach (Config::getLocVersions() as $i => $v1) {
      foreach (Config::getLocVersions() as $j => $v2) {
        if ($i > $j) {
          @unlink($this->getTxtDiffFileName($v1, $v2));
          @unlink($this->getZipDiffFileName($v1, $v2));
        }
      }
    }
  }

  function run() {
    $this->cleanup();

    // Write forms
    Log::info('** dumping ' . $this->getName());
    foreach (Config::getLocVersions() as $v) {
      if (!$v->freezeTimestamp || $this->allVersions) {
        Log::info("* dumping version {$v->name}");
        LocVersion::changeDatabase($v->name);

        $txtFile = $this->getTxtFileName($v);
        $zipFile = $this->getZipFileName($v);
        $ftpFile = $this->getFtpFileName($v);
        $this->writeForms($txtFile);
        $this->postProcess($txtFile);
        $this->zipAndShip($txtFile, $zipFile, $ftpFile);
      }
    }

    // Write diffs
    foreach (Config::getLocVersions() as $i => $v1) {
      foreach (Config::getLocVersions() as $j => $v2) {
        if (($i > $j) && (!$v2->freezeTimestamp || $this->allVersions)) {
          Log::info("* computing diffs between {$v1->name} and {$v2->name}");
          $this->downloadAndUnzip($v1);
          $this->downloadAndUnzip($v2);
          $txt1 = $this->getTxtFileName($v1);
          $txt2 = $this->getTxtFileName($v2);
          $diffTxt = $this->getTxtDiffFileName($v1, $v2);
          $diffZip = $this->getZipDiffFileName($v1, $v2);
          $diffFtp = $this->getFtpDiffFileName($v1, $v2);
          OS::executeAndAssert("diff $txt1 $txt2 | grep '[<>]' > $diffTxt || true");
          $this->zipAndShip($diffTxt, $diffZip, $diffFtp);
        }
      }
    }

    $this->cleanup();
  }
}

class BaseFormList extends FormList {

  function getName() {
    return 'base forms';
  }

  function getFilePattern() {
    return '%s/loc-baza-%s.%s';
  }

  function getDiffPattern() {
    return '%s/loc-dif-baza-%s-%s.%s';
  }

  function getQuery() {
    return
      'select form, modelType, modelNumber, restriction '.
      'from Lexem ' .
      'where isLoc ' .
      'order by formNoAccent asc, modelType asc, modelNumber asc';
  }

  function format($row) {
    return Str::padRight($row['form'], 20) .
      Str::padRight($row['modelType'], 4) .
      Str::padRight($row['modelNumber'], 8) .
      $row['restriction'];
  }

  function postProcess($fileName) {
  }
}

class InflectedFormList extends FormList {

  function getName() {
    return 'inflected forms';
  }

  function getFilePattern() {
    return '%s/loc-flexiuni-%s.%s';
  }

  function getDiffPattern() {
    return '%s/loc-dif-flexiuni-%s-%s.%s';
  }

  function getQuery() {
    return
      'select distinct I.formNoAccent ' .
      'from InflectedForm I, Lexem L, Model M, ModelDescription MD, ModelType MT ' .
      'where I.lexemId = L.id ' .
      'and L.modelType = MT.code ' .
      'and MT.canonical = M.modelType ' .
      'and L.modelNumber = M.number ' .
      'and M.id = MD.modelId ' .
      'and MD.inflectionId = I.inflectionId ' .
      'and MD.variant = I.variant ' .
      'and MD.applOrder = 0 ' .
      'and L.isLoc and MD.isLoc ' .
      'order by I.formNoAccent asc';
  }

  function format($row) {
    return $row['formNoAccent'];
  }

  function postProcess($fileName) {
  }
}

class ReducedFormList extends FormList {

  function getName() {
    return 'reduced forms';
  }

  function getFilePattern() {
    return '%s/loc-reduse-%s.%s';
  }

  function getDiffPattern() {
    return '%s/loc-dif-reduse-%s-%s.%s';
  }

  function getQuery() {
    return
      'select I.formNoAccent ' .
      'from InflectedForm I, Lexem L, Model M, ModelDescription MD, ModelType MT ' .
      'where I.lexemId = L.id ' .
      'and L.modelType = MT.code ' .
      'and MT.canonical = M.modelType ' .
      'and L.modelNumber = M.number ' .
      'and M.id = MD.modelId ' .
      'and MD.inflectionId = I.inflectionId ' .
      'and MD.variant = I.variant ' .
      'and MD.applOrder = 0 ' .
      'and L.isLoc and MD.isLoc ' .
      'and char_length(I.formNoAccent) between 2 and 15';
  }

  function format($row) {
    return $row['formNoAccent'];
  }

  function postProcess($fileName) {
    $tmpFile = tempnam($this->tmpDir, 'loc_');

    Log::info('* removing diacritics');
    $s = file_get_contents($fileName);
    $s = Str::unicodeToLatin($s);
    file_put_contents($tmpFile, $s);

    Log::info('* removing duplicates and sorting');
    OS::executeAndAssert("sort -u {$tmpFile} -o {$fileName}");

    unlink($tmpFile);
  }
}

?>

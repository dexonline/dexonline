<?php
require_once("../phplib/util.php");
ini_set('max_execution_time', '3600');
$locVersion = util_getRequestParameter('locVersion');
$newLocVersion = util_getRequestParameter('newLocVersion');

if ($newLocVersion) {
  if ($locVersion == $newLocVersion) {
    FlashMessage::add('Ați selectat aceeași versiune LOC de două ori');
    util_redirect('scrabble-loc');
  }

  $file1 = tempnam('/tmp', 'loc_diff_');
  $file2 = tempnam('/tmp', 'loc_diff_');
  writeLexems($locVersion, $file1);
  writeLexems($newLocVersion, $file2);
  $diff = OS::executeAndReturnOutput("diff $file1 $file2 || true");

  print "<pre>\n";
  foreach ($diff as $line) {
    if (StringUtil::startsWith($line, '< ')) {
      print sprintf("<span style=\"color: red\">%s: %s</span>\n", $locVersion, substr($line, 2));
    } else if (StringUtil::startsWith($line, '> ')) {
      print sprintf("<span style=\"color: green\">%s: %s</span>\n", $newLocVersion, substr($line, 2));
    }
  }
  print "</pre>\n";

  util_deleteFile($file1);
  util_deleteFile($file2);
  exit;
}

if ($locVersion) {
  header('Content-type: text/plain; charset=UTF-8');
  writeLexems($locVersion, 'php://output');
  exit;
}

setlocale(LC_ALL, "ro_RO.utf8");
SmartyWrap::assign('locVersions', Config::getLocVersions());
SmartyWrap::assign('page_title', 'Lista Oficială de Cuvinte');
SmartyWrap::displayCommonPageWithSkin('scrabble-loc.ihtml');

function writeLexems($locVersion, $fileName) {
  LocVersion::changeDatabase($locVersion);

  $query = 'select L.form, LM.modelType, LM.modelNumber, LM.restriction '.
    'from Lexem L join LexemModel LM on L.id = LM.lexemId ' .
    'where LM.isLoc ' .
    'order by L.formNoAccent asc';
  $dbResult = db_execute($query, PDO::FETCH_ASSOC);
  $handle = fopen($fileName, 'w');
  foreach ($dbResult as $r) {
    fprintf($handle, AdminStringUtil::padRight(mb_strtoupper($r['form']), 20));
    fprintf($handle, AdminStringUtil::padRight($r['modelType'], 4));
    fprintf($handle, AdminStringUtil::padRight($r['modelNumber'], 8));
    fprintf($handle, $r['restriction'] . "\n");
  }
  fclose($handle);
}

?>

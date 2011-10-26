<?php
require_once("../phplib/util.php");
ini_set('max_execution_time', '3600');
define('DB_QUERY', 'select * from Lexem where isLoc order by formNoAccent');
$locVersion = util_getRequestParameter('locVersion');
$newLocVersion = util_getRequestParameter('newLocVersion');

if ($newLocVersion) {
  if ($locVersion == $newLocVersion) {
    flash_add('Ați selectat aceeași versiune LOC de două ori');
    util_redirect('scrabble-loc');
  }

  $file1 = tempnam('/tmp', 'loc_diff_');
  $file2 = tempnam('/tmp', 'loc_diff_');
  writeLexems($locVersion, $file1);
  writeLexems($newLocVersion, $file2);
  $diff = os_executeAndReturnOutput("diff $file1 $file2 || true");

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
smarty_assign('locVersions', pref_getLocVersions());
smarty_displayCommonPageWithSkin('scrabble-loc.ihtml');

function writeLexems($locVersion, $fileName) {
  LocVersion::changeDatabase($locVersion);
  $dbResult = db_execute(DB_QUERY);
  $handle = fopen($fileName, 'w');
  while (!$dbResult->EOF) {
    $l = new Lexem();
    $l->set($dbResult->fields);
    $dbResult->MoveNext();
    fprintf($handle, AdminStringUtil::padRight(mb_strtoupper($l->form), 20));
    fprintf($handle, AdminStringUtil::padRight($l->modelType, 4));
    fprintf($handle, AdminStringUtil::padRight($l->modelNumber, 8));
    fprintf($handle, $l->restriction . "\n");
  }
  fclose($handle);
}

?>

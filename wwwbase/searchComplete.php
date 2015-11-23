<?php
require_once("../phplib/util.php");
header("Content-Type: application/json");


$acEnable = Config::get("search.acEnable");
$acMinChars = Config::get("search.acMinChars");
$acLimit = Config::get("search.acLimit");

$search = util_getRequestParameter('term');

if (!$acEnable || strlen($search) < $acMinChars) {
  return print(json_encode(array()));
}

$hasDiacritics =
  session_user_prefers(Preferences::FORCE_DIACRITICS) ||
  StringUtil::hasDiacritics($search);

$search_results = Lexem::searchLike("{$search}%", $hasDiacritics, true, $acLimit);

$clean_results = array_map(function($rec) {
  return $rec['formNoAccent'];
}, $search_results);


return print(json_encode($clean_results));

?>


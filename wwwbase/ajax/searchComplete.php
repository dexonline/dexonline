<?php
require_once("../../phplib/Core.php");
header("Content-Type: application/json");

$acEnable = Config::get("search.acEnable");
$acMinChars = Config::get("search.acMinChars");
$acLimit = Config::get("search.acLimit");

$term = Request::get('term');

if (!$acEnable || strlen($term) < $acMinChars) {
  return print(json_encode(array()));
}

$forms = Autocomplete::ac($term, $acLimit);

print json_encode($forms);

?>

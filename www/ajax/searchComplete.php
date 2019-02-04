<?php
require_once '../../phplib/Core.php';
header("Content-Type: application/json");

$term = Request::get('term');

$use = Config::SEARCH_AC_ENABLED &&
  (mb_strlen($term) >= Config::SEARCH_AC_MIN_CHARS);

$forms = $use ? Autocomplete::ac($term, Config::SEARCH_AC_LIMIT) : [];

print json_encode($forms);

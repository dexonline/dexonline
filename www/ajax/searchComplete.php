<?php
require_once '../../lib/Core.php';
header("Content-Type: application/json");

$term = Request::get('term');
$type = Request::get('type');

$use = Config::SEARCH_AC_ENABLED &&
  (mb_strlen($term) >= Config::SEARCH_AC_MIN_CHARS);

$forms = $use ? Autocomplete::ac($term, Config::SEARCH_AC_LIMIT) : [];

if ($type === 'opensearch') {
  print json_encode([$term, $forms]);
} else {
  print json_encode($forms);
}

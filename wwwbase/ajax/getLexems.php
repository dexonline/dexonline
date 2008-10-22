<?
require_once("../../phplib/util.php");

$query = util_getRequestParameter('query');

$parts = split('\(', $query, 2);

$name = trim($parts[0]);

if (count($parts) == 2) {
  $description = trim($parts[1]);
  $description = str_replace(')', '', $description);
  $lexems = Lexem::loadByUnaccentedPartialDescription($name, $description);
} else {
  $lexems = Lexem::loadByPartialUnaccented($name);
}

foreach ($lexems as $l) {
  $s = ($l->description)
    ? $l->unaccented . ' (' . $l->description . ')'
    : $l->unaccented;
  print $l->id . "\n";
  print "$s\n";
}

?>

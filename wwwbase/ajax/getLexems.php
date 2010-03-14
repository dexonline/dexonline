<?
require_once("../../phplib/util.php");

$query = util_getRequestParameter('query');
$parts = split('\(', $query, 2);
$name = trim($parts[0]);

if (count($parts) == 2) {
  $description = trim($parts[1]);
  $description = str_replace(')', '', $description);
  $lexems = db_find(new Lexem(), "formNoAccent = '{$name}' and description like '{$description}%' order by formNoAccent, description limit 10");
} else {
  $lexems = db_find(new Lexem(), "formNoAccent like '{$name}%' order by formNoAccent limit 10");
}

foreach ($lexems as $l) {
  print "{$l->id}\n{$l}\n";
}

?>

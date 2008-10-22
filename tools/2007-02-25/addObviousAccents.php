<?
require_once("../../phplib/util.php");
assert_options(ASSERT_BAIL, 1);
debug_off();

$count = 0;
$fixed = 0;

$dbResult = mysql_query('select * from lexems ' .
                        'where lexem_forma not rlike "\'"');
print "Adding accents to lexems with a single vowel.\n";
print "Examining " . mysql_num_rows($dbResult) . " lexems.\n";

while ($dbRow = mysql_fetch_assoc($dbResult)) {
  $l = Lexem::createFromDbRow($dbRow);

  if (text_countVowels($l->form) == 1) {
    $l->form = text_placeAccent($l->form, 1, '');
    $l->save();
    //print "Fixed: {$l->form}\n";
    $fixed ++;
  }

  $count++;
}

print "$count lexems seen, $fixed fixed.\n";

?>

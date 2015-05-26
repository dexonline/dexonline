<?php
require_once("../phplib/util.php");
setlocale(LC_ALL, "ro_RO.utf8");
header('Content-Type: application/json');

$search = $_REQUEST['term'];

if (strlen($search) < 3) {
    return print(json_encode(array()));
}


$LIKE = sprintf("%s%%", $search);



$arr = StringUtil::analyzeQuery($search);
$hasDiacritics = session_user_prefers(Preferences::FORCE_DIACRITICS) || $arr[0];

$lexems = Lexem::searchLikeInflectedForms($LIKE, $hasDiacritics, true);

// Alternate search directly and only in the Lexem table
//$lexems = ORM::for_table('Lexem')
//    ->where_like('formNoAccent', $LIKE)
//    ->order_by_asc('formNoAccent')
//    ->limit(5)
//    ->find_many();

$clean_results = array_map(function($lexem) use ($hasDiacritics){
    if ($hasDiacritics) {
        return $lexem->formNoAccent;
    } else {
        return $lexem->formUtf8General;
    }
}, $lexems);

return print(json_encode($clean_results));

?>


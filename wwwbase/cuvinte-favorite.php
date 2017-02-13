<?php
require_once("../phplib/util.php");

$user = session_getUser();
if (!$user) {
  util_redirect('auth/login');
}
$definitions = Model::factory('Definition')
  ->table_alias('d')
  ->select('d.*')
  ->join('UserWordBookmark', ['d.id', '=', 'uwb.definitionId'], 'uwb')
  ->where('uwb.userId', $user->id)
  ->order_by_asc('d.lexicon')
  ->find_many();
$results = SearchResult::mapDefinitionArray($definitions);

SmartyWrap::assign('results', $results);
SmartyWrap::display('cuvinte-favorite.tpl');
?>

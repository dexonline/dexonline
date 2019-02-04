<?php
require_once '../phplib/Core.php';

$user = User::getActive();
if (!$user) {
  Util::redirect('auth/login');
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

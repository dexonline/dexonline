<?php
require_once("../../phplib/util.php"); 
util_assertModerator(PRIV_EDIT);
util_assertNotMirror();

$submitButton = util_getRequestParameter('submitButton');

if ($submitButton) {
  foreach ($_REQUEST as $name => $position) {
    if (StringUtil::startsWith($name, 'position_')) {
      $parts = preg_split('/_/', $name);
      assert(count($parts) == 2);
      assert($parts[0] == 'position');
      $lexem = Lexem::get_by_id($parts[1]);
      $noAccent = util_getRequestParameter('noAccent_' . $lexem->id);

      if ($noAccent) {
        $lexem->noAccent = 1;
        $lexem->save();
      } else if ($position != -1) {
        $lexem->form = mb_substr($lexem->form, 0, $position) . "'" . mb_substr($lexem->form, $position);
        $lexem->save();
        $lexem->regenerateParadigm();
      }
    }
  }
  util_redirect("placeAccents.php");
}

$chars = array();
$searchResults = array();
$lexems = Model::factory('Lexem')->raw_query("select * from Lexem where form not rlike '\'' and not noAccent order by rand() limit 10")
  ->find_many();
foreach($lexems as $l) {
  $charArray = array();
  $form = mb_strtoupper($l->form);
  $len = mb_strlen($form);
  for ($i = 0; $i < $len; $i++) {
    $c = StringUtil::getCharAt($form, $i);;
    $charArray[] = ctype_space($c) ? '&nbsp;' : $c;
  }
  $chars[$l->id] = $charArray;

  $definitions = Definition::loadByEntryId($l->entryId);
  $searchResults[$l->id] = SearchResult::mapDefinitionArray($definitions);
}

RecentLink::createOrUpdate('Plasare accente');
SmartyWrap::assign('lexems', $lexems);
SmartyWrap::assign('chars', $chars);
SmartyWrap::assign('searchResults', $searchResults);
SmartyWrap::assign('recentLinks', RecentLink::loadForUser());
SmartyWrap::displayAdminPage('admin/placeAccents.tpl');

?>

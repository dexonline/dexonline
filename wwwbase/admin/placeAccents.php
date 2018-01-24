<?php
require_once("../../phplib/Core.php"); 
User::mustHave(User::PRIV_EDIT);
Util::assertNotMirror();

$saveButton = Request::has('saveButton');

if ($saveButton) {
  foreach ($_REQUEST as $name => $position) {
    if (Str::startsWith($name, 'position_')) {
      $parts = preg_split('/_/', $name);
      assert(count($parts) == 2);
      assert($parts[0] == 'position');
      $lexeme = Lexeme::get_by_id($parts[1]);
      $noAccent = Request::get('noAccent_' . $lexem->id);

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
  Util::redirect("placeAccents.php");
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
    $c = Str::getCharAt($form, $i);;
    $charArray[] = ctype_space($c) ? '&nbsp;' : $c;
  }
  $chars[$l->id] = $charArray;

  $definitions = Definition::loadByEntryIds($l->getEntryIds());
  $searchResults[$l->id] = SearchResult::mapDefinitionArray($definitions);
}

SmartyWrap::assign('lexems', $lexems);
SmartyWrap::assign('chars', $chars);
SmartyWrap::assign('searchResults', $searchResults);
SmartyWrap::addCss('admin');
SmartyWrap::display('admin/placeAccents.tpl');

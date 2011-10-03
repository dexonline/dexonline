<?php
require_once("../../phplib/util.php"); 
util_assertModerator(PRIV_EDIT);
util_assertNotMirror();

$submitButton = util_getRequestParameter('submitButton');

if ($submitButton) {
  foreach ($_REQUEST as $name => $position) {
    if (text_startsWith($name, 'position_')) {
      $parts = preg_split('/_/', $name);
      assert(count($parts) == 2);
      assert($parts[0] == 'position');
      $lexem = Lexem::get("id = " . $parts[1]);
      $noAccent = util_getRequestParameter('noAccent_' . $lexem->id);

      if ($noAccent) {
        //print "No accent on [{$lexem->form}]<br/>\n";
        $lexem->noAccent = 1;
        $lexem->save();
      } else if ($position != -1) {
        $lexem->form = mb_substr($lexem->form, 0, $position) . "'" .
          mb_substr($lexem->form, $position);
        //print "[{$lexem->form}]<br/>\n";
        $lexem->save();
        $lexem->regenerateParadigm();
      }
    }
  }
  util_redirect("placeAccents.php");
}

$chars = array();
$searchResults = array();
$lexems = db_find(new Lexem(), "form not rlike '\'' and not noAccent order by rand() limit 10");
foreach($lexems as $l) {
  $charArray = array();
  $form = text_unicodeToUpper($l->form);
  $len = mb_strlen($form);
  for ($i = 0; $i < $len; $i++) {
    $c = text_getCharAt($form, $i);;
    $charArray[] = ctype_space($c) ? '&nbsp;' : $c;
  }
  $chars[$l->id] = $charArray;

  $definitions = Definition::loadByLexemId($l->id);
  $searchResults[$l->id] = SearchResult::mapDefinitionArray($definitions);
}

RecentLink::createOrUpdate('Plasare accente');
smarty_assign('sectionTitle', 'Plasare accente');
smarty_assign('lexems', $lexems);
smarty_assign('chars', $chars);
smarty_assign('searchResults', $searchResults);
smarty_assign("allStatuses", util_getAllStatuses());
smarty_assign('recentLinks', RecentLink::loadForUser());
smarty_displayWithoutSkin('flex/placeAccents.ihtml');

?>

<?
require_once("../../phplib/util.php"); 
util_assertModeratorStatus();
util_assertNotMirror();
util_hideEmptyRequestParameters();

$modelType = util_getRequestParameter('modelType');
$submitButton = util_getRequestParameter('submitButton');

if ($submitButton) {
  $lexemsToDelete = array();
  foreach ($_REQUEST as $name => $value) {
    if (text_startsWith($name, 'merge_') && $value) {
      $parts = split('_', $name);
      assert(count($parts) == 3);
      assert($parts[0] == 'merge');
      $src = Lexem::load($parts[1]);
      $dest = Lexem::load($parts[2]);

      // Merge $src into $dest
      $defs = Definition::loadByLexemId($src->id);
      foreach ($defs as $def) {
        LexemDefinitionMap::associate($dest->id, $def->id);
      }

      // Delay the deletion because we might have to merge $src with other lexems.
      $lexemsToDelete[] = $src;
    }
  }
  foreach ($lexemsToDelete as $lexem) {
    $lexem->delete();
  }
  util_redirect("mergeLexems.php?modelType={$modelType}");
  exit;
}

$PLURAL_INFLECTIONS = array(3, 11, 19, 27, 35);
$dbResult = db_selectPluralLexemsByModelType($modelType);

$lexems = array();
while ($row = mysql_fetch_assoc($dbResult)) {
  $lexem = Lexem::createFromDbRow($row);
  $lexem->matches = array();
  $wordLists = WordList::loadByUnaccented($lexem->unaccented);

  foreach ($wordLists as $wl) {
    if (in_array($wl->inflectionId, $PLURAL_INFLECTIONS) && $wl->lexemId != $lexem->id) {
      $lexem->matches[] = Lexem::load($wl->lexemId);
    }
  }

  if (count($lexem->matches)) {
    $lexems[] = $lexem;
  }
}

RecentLink::createOrUpdate('Unificare lexeme');
smarty_assign('sectionTitle', 'Unificare lexeme');
smarty_assign('recentLinks', RecentLink::loadForUser());
smarty_assign('modelType', $modelType);
smarty_assign('lexems', $lexems);
smarty_displayWithoutSkin('flex/mergeLexems.ihtml');

?>

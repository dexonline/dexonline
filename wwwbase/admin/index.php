<?php
require_once("../../phplib/util.php");
util_assertModeratorStatus();
util_assertNotMirror();

$numUnassociatedLexems = Lexem::countUnassociated();
$numUnassociatedDefinitions = Definition::countUnassociated();
$numDefinitionsWithTypos = Definition::countHavingTypos();
$numTemporaryDefinitions = Definition::countByStatus(ST_PENDING);
$numTemporaryLexems = Lexem::countTemporary();
$numLexemsWithComments = Lexem::countHavingComments();
$numLexemsWithoutAccents = Lexem::countWithoutAccents();
// Disable this for now, it slows down the admin page.
// $numAmbiguousLexems = Lexem::countAmbiguous();
$numAmbiguousLexems = 0;

$models = Model::loadByType('A');

smarty_assign('numUnassociatedLexems', $numUnassociatedLexems);
smarty_assign('numUnassociatedDefinitions', $numUnassociatedDefinitions);
smarty_assign('numDefinitionsWithTypos', $numDefinitionsWithTypos);
smarty_assign('numTemporaryDefinitions', $numTemporaryDefinitions);
smarty_assign('numTemporaryLexems', $numTemporaryLexems);
smarty_assign('numLexemsWithComments', $numLexemsWithComments);
smarty_assign('numLexemsWithoutAccents', $numLexemsWithoutAccents);
smarty_assign('numAmbiguousLexems', $numAmbiguousLexems);
smarty_assign('recentLinks', RecentLink::loadForUser());
smarty_assign("allStatuses", util_getAllStatuses());
smarty_assign("allModeratorSources", Source::loadAllModeratorSources());
smarty_assign('modelTypes', ModelType::loadCanonical());
smarty_assign('models', $models);
smarty_displayWithoutSkin('admin/index.ihtml');

?>

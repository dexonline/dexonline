<?php

require_once("../../phplib/Core.php");
User::mustHave(User::PRIV_EDIT);
Util::assertNotMirror();

$defs = Model::factory('Definition')
      ->table_alias('d')
      ->select('d.*')
      ->left_outer_join('EntryDefinition', ['d.id', '=', 'ed.definitionId'], 'ed')
      ->where_not_equal('d.status', Definition::ST_DELETED)
      ->where_null('ed.id')
      ->find_many();

SmartyWrap::assign('searchResults', SearchResult::mapDefinitionArray($defs));
SmartyWrap::addCss('admin');
SmartyWrap::display('admin/viewUnassociatedDefinitions.tpl');

?>

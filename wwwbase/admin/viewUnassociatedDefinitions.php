<?php

require_once '../../phplib/Core.php';
User::mustHave(User::PRIV_EDIT);

$associateButton = Request::has('associateButton');

if ($associateButton) {
  $defIds = Request::get('associateDefinitionIds');
  $defIds = array_filter(explode(',', $defIds));
  $entryIds = Request::getArray('associateEntryIds');
  foreach ($defIds as $defId) {
    foreach ($entryIds as $entryId) {
      EntryDefinition::associate($entryId, $defId);
      Log::info("Associated entry {$entryId} with definition {$defId}");
    }
  }
  FlashMessage::add(sprintf('Am asociat %d definiții cu %d intrări.',
                            count($defIds), count($entryIds)),
                    'success');
  Util::redirect("viewUnassociatedDefinitions.php");
}

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

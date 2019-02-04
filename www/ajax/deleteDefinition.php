<?php
require_once '../../lib/Core.php';
User::mustHave(User::PRIV_EDIT);

$defId = Request::get('id');
$def = Definition::get_by_id($defId);
if ($def && $def->id) {
  $def->status = Definition::ST_DELETED;
  $def->save();
  EntryDefinition::dissociateDefinition($def->id);
  Typo::delete_all_by_definitionId($def->id);
  Log::notice("Marked definition {$def->id} ({$def->lexicon}) as deleted");
}

<?php
require_once("../phplib/Core.php");

User::mustHave(User::PRIV_EDIT);

$id = Request::get('id');
$def = Definition::get_by_id($id);

if (!$def) {
  FlashMessage::add("Nu există nicio definiție cu ID-ul {$id}.");
  Util::redirect("index.php");
}

$prev = null;
$changeSets = [];

$dvs = Model::factory('DefinitionVersion')
     ->where('definitionId', $id)
     ->order_by_asc('id')
     ->find_many();

foreach ($dvs as $dv) {
  if ($prev) {
    compareVersions($prev, $dv, $changeSets);
  }
  $prev = $dv;
}

// And once more for the current version
if ($prev) {
  $current = DefinitionVersion::current($def);
  compareVersions($prev, $current, $changeSets);
}

$changeSets = array_reverse($changeSets); // newest changes first

SmartyWrap::assign('def', $def);
SmartyWrap::assign('changeSets', $changeSets);
SmartyWrap::display('istoria-definitiei.tpl');

/*************************************************************************/

function compareVersions(&$old, &$new, &$changeSets) {

  if (($old->sourceId != $new->sourceId) ||
      ($old->status != $new->status) ||
      ($old->lexicon != $new->lexicon) ||
      ($old->internalRep != $new->internalRep)) {

    $changeSet = [
      'old' => $old,
      'new' => $new,
      'user' => User::get_by_id($new->modUserId),
      'oldSource' => Source::get_by_id($old->sourceId),
      'newSource' => Source::get_by_id($new->sourceId),
    ];

    if ($old->internalRep != $new->internalRep) {
      $changeSet['diff'] = LDiff::htmlDiff($old->internalRep, $new->internalRep);
    }

    $changeSets[] = $changeSet;
  }

}

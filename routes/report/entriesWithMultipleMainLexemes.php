<?php

User::mustHave(User::PRIV_STRUCT);

$saveButton = Request::has('saveButton');

if ($saveButton) {
  // All entry IDs appear in $entryIds. The ones with checkmarks also appear
  // in $multipleMains.
  $entryIds = Request::getArray('entryIds');
  $multipleMains = Request::getArray('multipleMains');

  $entries = Model::factory('Entry')
    ->where_in('id', $entryIds)
    ->find_many();

  foreach ($entries as $e) {
    $newValue = in_array($e->id, $multipleMains);
    if ($newValue != $e->multipleMains) {
      Log::info("Toggled multipleMains for entry #{$e->id} $e->description.");
      $e->multipleMains = !$e->multipleMains;
      $e->save();
    }
  }

  Util::redirect('entriesWithMultipleMainLexemes');
}

$numEntries = Entry::loadWithMultipleMainLexemes();
$entries = Entry::loadWithMultipleMainLexemes($onlyCount = false);

Smart::assign([
  'numEntries' => $numEntries,
  'entries' => $entries,
]);
Smart::addResources('admin', 'tablesorter');
Smart::display('report/entriesWithMultipleMainLexemes.tpl');

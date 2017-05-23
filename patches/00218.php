<?php

// Convert text preferences to bitwise masks

$users = Model::factory('User')->find_many();

$map = array_flip(Preferences::$NAMES);

foreach ($users as $u) {
  if ($u->textPreferences) {
    $u->preferences = 0;
    foreach (explode(',', $u->textPreferences) as $pref) {
      $u->preferences += $map[$pref];
    }
    print "Id {$u->id} value {$u->preferences}\n";
    $u->save();
  }
}

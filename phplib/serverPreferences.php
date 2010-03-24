<?php
  /**
   * Handles Java-style property files. If a property value contains a comma,
   * we split it and map the property name to the resulting list.
   */
pref_loadPreferences(util_getRootPath() . "dex.conf");

function pref_loadPreferences($fileName) {
  if (!($lines = file($fileName))) {
    return false;
  }

  $prefs = array();
  foreach ($lines as $line_num => $line) {
    list($var, $value) = split("=", trim($line), 2);
    if (!empty($var)) {
      if (strstr($value, ",")) {
	$prefs[$var] = split(",", $value);
      } else {
	$prefs[$var] = $value;
      }
    }
  }

  $GLOBALS['serverPreferences'] = $prefs;
}

function pref_getServerPreference($name) {
  if (array_key_exists($name, $GLOBALS['serverPreferences'])) {
    return $GLOBALS['serverPreferences'][$name];
  } else {
    return false;
  }
}

function pref_isMirror() {
  return pref_getServerPreference('mirror');
}

function pref_getLocPrefix() {
  return pref_getServerPreference('mysql_loc_prefix');
}

function pref_getContactEmail() {
  return pref_getServerPreference('contact');
}

function pref_getDebugUser() {
  return pref_getServerPreference('debugUser');
}

function pref_getHostedBy() {
  return pref_getServerPreference('hostedBy');
}

function pref_getSmartyClass() {
  return pref_getServerPreference('smartyClass');
}

function pref_getLocVersions() {
  if (!array_key_exists('locVersions', $GLOBALS)) {
    $result = array();
    $locParts = pref_getServerPreference('locVersions');
    if (!is_array($locParts)) {
      $locParts = split(',', $locParts);
    }
    foreach ($locParts as $part) {
      $part = trim($part);
      if ($part) {
	$versionAndDate = split(' ', $part);
	assert(count($versionAndDate == 2));
	$lv = new LocVersion();
	$lv->name = trim($versionAndDate[0]);
	$date = trim($versionAndDate[1]);
	$lv->freezeTimestamp = ($date == 'current') ? null : strtotime($date);
	$result[] = $lv;
      }
    }
    $GLOBALS['locVersions'] = $result;
  }
  return $GLOBALS['locVersions'];
}

function pref_getFrozenLocVersions() {
  // Return all versions but the current one.
  $lvs = pref_getLocVersions();
  assert(count($lvs) >= 2);
  assert(!$lvs[count($lvs) - 1]->freezeTimestamp);
  return array_slice($lvs, 0, -1);
}

function pref_getAdsense() {
  return pref_getServerPreference('adsense');  
}

?>

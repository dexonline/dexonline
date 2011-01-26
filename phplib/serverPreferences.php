<?php
  /**
   * Handles Java-style property files. If a property value contains a comma,
   * we preg_split it and map the property name to the resulting list.
   */
$GLOBALS['serverPreferences'] = parse_ini_file(util_getRootPath() . "dex.conf");

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
    foreach ($locParts as $part) {
      $part = trim($part);
      if ($part) {
        $versionAndDate = preg_split('/ /', $part);
        assert(count($versionAndDate == 2));
        $lv = new LocVersion();
        $lv->name = trim($versionAndDate[0]);
        $date = trim($versionAndDate[1]);
        $lv->freezeTimestamp = ($date == 'current') ? null : strtotime($date);
        $result[] = $lv;
      }
    }
    $GLOBALS['locVersions'] = array_reverse($result);
  }
  return $GLOBALS['locVersions'];
}

?>

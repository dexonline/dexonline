<?php
/**
 * Handles Java-style property files. If a property contains URL-style parameters (var=value1&var2=value2&...),
 * we parse_str it and map the property name to the resulting associative array.
 */
pref_parsePreferenceFile();

function pref_parsePreferenceFile() {
  $raw = parse_ini_file(util_getRootPath() . "dex.conf", true);
  $processed = array();
  foreach ($raw as $key => $value) {
    if (is_array($value)) {
      $processed[$key] = array();
      foreach ($value as $key2 => $value2) {
        $processed[$key][$key2] = (strpos($value2, '&') === false) ? $value2 : text_parseStr($value2);
      }
    } else {
      $processed[$key] = (strpos($value, '&') === false) ? $value : text_parseStr($value);
    }
  }
  $GLOBALS['serverPreferences'] = $processed;
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

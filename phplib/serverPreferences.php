<?php
/**
 * Handles Java-style property files. If a property contains URL-style parameters (var=value1&var2=value2&...),
 * we parse_str it and map the property name to the resulting associative array.
 */
pref_parsePreferenceFile();

function pref_parsePreferenceFile() {
  $raw = parse_ini_file(util_getRootPath() . "dex.conf", true);
  _pref_traverseRecursivePreferences($raw);
  $GLOBALS['serverPreferences'] = $raw;
}

function _pref_traverseRecursivePreferences(&$pref) {
  foreach ($pref as $key => $value) {
    if (is_array($value)) {
      _pref_traverseRecursivePreferences($value);
      $pref[$key] = $value;
    } else {
      $pref[$key] = (strpos($value, '&') === false) ? $value : StringUtil::parseStr($value);
    }
  }
}

function pref_getServerPreference($name) {
  if (array_key_exists('global', $GLOBALS['serverPreferences']) && array_key_exists($name, $GLOBALS['serverPreferences']['global'])) {
    return $GLOBALS['serverPreferences']['global'][$name];
  } else if (array_key_exists($name, $GLOBALS['serverPreferences'])) {
    // DEPRECATED. Python requires all INI file options to be contained in sections
    // We don't mind that, but we offer a fallback until people upgrade to the new config.
    return $GLOBALS['serverPreferences'][$name];
  } else {
    return false;
  }
}

function pref_getSectionPreference($section, $name, $defaultValue = NULL) {
  if (array_key_exists($section, $GLOBALS['serverPreferences']) && array_key_exists($name, $GLOBALS['serverPreferences'][$section])) {
    return $GLOBALS['serverPreferences'][$section][$name];
  }
  else if (!is_null($defaultValue)) {
    return $defaultValue;
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

function pref_getMaxBookmarks() {
  return pref_getServerPreference('maxBookmarks');
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

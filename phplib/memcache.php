<?php

function mc_init() {
  $GLOBALS['memcache'] = false;
  if (!mc_shouldUse()) {
    return;
  }
  $memcache = new Memcache;
  if (!$memcache->connect('localhost', 11211)) {
    return;
  }
  $GLOBALS['memcache'] = $memcache;
}

/**
 * Logic that tells us whether it's ok to use the memcached server.
 * We don't want it for moderators because they are the ones who can alter the results and we want them to see the changes right away.
 */
function mc_shouldUse() {
  return pref_getServerPreference('memcache') && !util_isModerator(PRIV_EDIT);
}

function mc_get($key) {
  if (!$GLOBALS['memcache'] || !$key) {
    return;
  }
  return $GLOBALS['memcache']->get($key);
}

function mc_set($key, $value) {
  if (!$GLOBALS['memcache'] || !$key || !$value) {
    return;
  }
  return $GLOBALS['memcache']->set($key, $value, false, 86400);
}

?>

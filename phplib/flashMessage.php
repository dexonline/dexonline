<?php

function flash_add($message, $type = 'error') {
  $oldMessage = array_key_exists('flashMessage', $GLOBALS) ? $GLOBALS['flashMessage'] : '';
  $GLOBALS['flashMessage'] = "{$oldMessage}{$message}<br/>";
  $GLOBALS['flashMessageType'] = $type;
}

function flash_getMessage() {
  return array_key_exists('flashMessage', $GLOBALS) ? $GLOBALS['flashMessage'] : null;
}

function flash_getMessageType() {
  return array_key_exists('flashMessageType', $GLOBALS) ? $GLOBALS['flashMessageType'] : null;
}

function flash_saveToSession() {
  if (array_key_exists('flashMessage', $GLOBALS)) {
    session_setVariable('flashMessage', $GLOBALS['flashMessage']);
    session_setVariable('flashMessageType', $GLOBALS['flashMessageType']);
  }
}

function flash_restoreFromSession() {
  if (($message = session_getWithDefault('flashMessage', null)) &&
      ($type = session_getWithDefault('flashMessageType', null))) {
    $GLOBALS['flashMessage'] = $message; // Already has a trailing <br/>
    $GLOBALS['flashMessageType'] = $type;
    session_unsetVariable('flashMessage');
    session_unsetVariable('flashMessageType');
  }
}

?>

<?php

class FlashMessage {
   // an array of [$text, $type] pairs, where $type follows Bootstrap conventions
  static $messages = [];

  static function add($message, $type = 'danger') {
    self::$messages[] = [
      'text' => $message,
      'type' => $type
    ];
  }

  static function getMessages() {
    return self::$messages;
  }

  static function hasMessages() {
    return count(self::$messages) > 0;
  }

  static function saveToSession() {
    if (count(self::$messages)) {
      session_setVariable('flashMessages', self::$messages);
    }
  }

  static function restoreFromSession() {
    if ($messages = session_get('flashMessages')) {
      self::$messages = $messages;
      session_unsetVariable('flashMessages');
    }
  }
}

?>

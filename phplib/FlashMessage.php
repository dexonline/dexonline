<?php

class FlashMessage {
   // an array of [$text, $type] pairs, where $type follows Bootstrap conventions
  static $messages = [];
  static $hasErrors = false;

  static function add($message, $type = 'danger') {
    self::$messages[] = [
      'text' => $message,
      'type' => $type
    ];
    self::$hasErrors |= ($type == 'danger');
  }

  static function getMessages() {
    return self::$messages;
  }

  static function hasErrors() {
    return self::$hasErrors;
  }

  static function saveToSession() {
    if (count(self::$messages)) {
      Session::set('flashMessages', self::$messages);
    }
  }

  static function restoreFromSession() {
    if ($messages = Session::get('flashMessages')) {
      self::$messages = $messages;
      Session::unset('flashMessages');
    }
  }
}

?>

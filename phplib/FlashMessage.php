<?php

class FlashMessage {
   // an array of [$text, $type] pairs, where $type follows Bootstrap conventions
  static $messages = [];
  static $hasErrors = false;

  /**
   * Adds messages to a message queue for later processing.
   *
   * @param string $message
   * @param string $type info, success, warning, danger (default)
   */
  static function add($message, $type = 'danger') {
    self::$messages[] = [
      'text' => $message,
      'type' => $type
    ];
    self::$hasErrors |= ($type == 'danger');
  }

  /**
   * Adds a more complex message that requires some templating.
   **/
  static function addTemplate($template, $args, $type = 'danger') {
    // TODO this overwrites previously assign variables. We really should instantiate a separate Smarty.
    foreach ($args as $key => $value) {
      SmartyWrap::assign($key, $value);
    }
    $message = SmartyWrap::fetch("alerts/{$template}");
    self::add($message, $type);
  }

  /**
   * Adds multiple messages. Each message can be a simple string or a
   * [template, args] pair.
   **/
  static function bulkAdd($messages, $type = 'danger') {
    foreach ($messages as $m) {
      if (is_string($m)) {
        FlashMessage::add($m, $type);
      } else {
        FlashMessage::addTemplate($m[0], $m[1], $type);
      }
    }
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
      Session::unsetVar('flashMessages');
    }
  }
}

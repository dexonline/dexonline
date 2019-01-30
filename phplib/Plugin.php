<?php

/**
 * Plugins are classes that may modify parts of a normal page's logic. Plugins
 * are defined in dex.conf and registered at initialization time in Core.php.
 * At various points during the code flow, plugins will be invited to make
 * modifications. All plugins are children of the Plugin class, which by
 * default does nothing.
 **/

abstract class Plugin {

  /* list of registered plugins */
  private static $plugins;

  static function init() {
    self::registerPlugins();
  }

  /* include and instantiate plugins defined in dex.conf */
  static function registerPlugins() {
    self::$plugins = [];
    $names = Config::get('plugins.plugin', []);

    foreach ($names as $plugin) {
      require_once __DIR__ . "/plugins/{$plugin}.php";
      self::$plugins[] = new $plugin;
    }
  }

  /* Notify registered plugins by invoking $method. Pass along all arguments (unpacked). */
  static function notify($method, &...$args) {
    foreach (self::$plugins as $plugin) {
      $plugin->$method(...$args);
    }
  }

  /** following are methods that plugins may choose to implement **/

  /* called when the HTML is rendering, at the end of the <head> tag */
  function htmlHead() {
  }

  /* called when the HTML is rendering, inside the <body> tag but before the site header */
  function bodyStart() {
  }

  /* called when the HTML is rendering, after the </body> tag */
  function afterBody() {
  }

  /* called before SmartyWrap::fetch(); plugins may include CSS/JS and assign Smarty variables */
  function cssJsSmarty() {
  }

  /* called at the start of a search for $query in search.php */
  function searchStart($query, $hasDiacritics) {
  }

  /* called after an entry ID search, once the entry and definitions are ready */
  function searchEntryId(&$definitions) {
  }

  /* called after a normal (inflected form) search, once the entry and definitions are ready */
  function searchInflected(&$definitions, $sourceId) {
  }
}

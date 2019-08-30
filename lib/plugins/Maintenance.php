<?php

/**
 * Maintenance mode.
 *
 * Sample config:
 *
 * const PLUGINS = [
 *   'Maintenance' => [], // no further config options
 * ];
 *
 **/

class Maintenance extends Plugin {

  function coreInit() {
    Smart::display('plugins/maintenance/maintenance.tpl', true);
    exit;
  }

}

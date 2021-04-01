<?php

/**
 * Force the pre-1993 orthography with an explanatory alert and navbar button.
 *
 * Sample config:
 *
 * const PLUGINS = [
 *   'Adina' => [
 *     // only run between these two dates
 *     'startDate' => '2021-04-01 00:00:00 GMT+3',
 *     'endDate' => '2021-04-01 23:59:59 GMT+3',
 *     'detailsUrl' => 'https://example.com',
 *   ],
 * ];
 *
 **/

class Adina extends Plugin {

  private $detailsUrl;
  private $run;

  public function __construct($cfg) {
    // run all the time by default
    $start = strtotime($cfg['startDate'] ?? '1970-01-01');
    $end = strtotime($cfg['endDate'] ?? '2100-12-31');
    $now = time();
    $this->run =
      ($now >= $start) &&
      ($now <= $end) &&
      !User::can(User::PRIV_ANY);;

    $this->detailsUrl = $cfg['detailsUrl'] ?? null;
  }

  function coreInit() {
    if ($this->run) {
      Smart::registerFilter('output', ['Str', 'replace_ai']);
    }
  }

  function cssJsSmarty() {
    if ($this->run) {
      Smart::addPluginCss('adina/main.css');
      Smart::assign('adinaDetailsUrl', $this->detailsUrl);
    }
  }

  function navbar() {
    if ($this->run) {
      print Smart::fetch('plugins/adina/navButton.tpl');
    }
  }

  function afterSearch() {
    if ($this->run) {
      print Smart::fetch('plugins/adina/afterSearch.tpl');
    }
  }

}

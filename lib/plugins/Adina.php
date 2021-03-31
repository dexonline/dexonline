<?php

/**
 * Force the pre-1993 orthography with an explanatory alert and navbar button.
 *
 * Sample config:
 *
 * const PLUGINS = [
 *   'Adina' => [
 *     'startDate' => '2021-04-01 00:00:00',
 *     'endDate' => '2021-04-01 23:59:59', 
 *     'detailsUrl' => 'https://example.com',
 *   ],
 * ];
 *
 **/

class Adina extends Plugin {

  /**
   * Only run between these timestamps.
   **/
  private $startTimestamp, $endTimestamp;
  private $detailsUrl;

  public function __construct($cfg) {
    // run all the time by default
    $this->startTimestamp = strtotime($cfg['startDate'] ?? '1970-01-01');
    $this->endTimestamp = strtotime($cfg['endDate'] ?? '2100-12-31');
    $this->detailsUrl = $cfg['detailsUrl'] ?? null;
  }

  function coreInit() {
    Smart::registerFilter('output', ['Str', 'replace_ai']);
  }

  function cssJsSmarty() {
    Smart::addPluginCss('adina/main.css');
    Smart::assign('adinaDetailsUrl', $this->detailsUrl);
  }

  function navbar() {
    print Smart::fetch('plugins/adina/navButton.tpl');
  }

  function afterSearch() {
    print Smart::fetch('plugins/adina/afterSearch.tpl');
  }

}

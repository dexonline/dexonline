<?php

/**
 * Call to action for occasional activism. Shown as a top banner colored in
 * red (Bootstrap danger color).
 *
 * Sample config:
 *
 * const PLUGINS = [
 *   'CallToAction' => [
 *     'template' => '2017-02-01.tpl'
 *   ],
 * ];
 *
 **/

class CallToAction extends Plugin {

  /* template name, relative to templates/plugins/callToAction/ */
  private $templateName;

  /* true if the user hid the banner (cookie-based) */
  private $visible;

  public function __construct($cfg) {
    $this->templateName = $cfg['template'];
    $this->hidden = isset($_COOKIE['hideCallToAction']);
  }

  function bodyStart() {
    if (!$this->hidden) {
      print Smart::fetch('plugins/callToAction/' . $this->templateName);
    }
  }

  function cssJsSmarty() {
    if (!$this->hidden) {
      Smart::addPluginCss('callToAction/callToAction.css');
      Smart::addPluginJs('callToAction/callToAction.js');
      Smart::addJs('cookie');
    }
  }
}

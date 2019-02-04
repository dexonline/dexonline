<?php

/**
 * April Fools' joke, 2016. Definitions appear slowly as if from a typewriter.
 * Currently accessible by adding ?typewriter=1 to URLs.
 *
 * Sample config:
 *
 * const PLUGINS = [
 *   'Typewriter' => [],
 * ];
 *
 **/

class Typewriter extends Plugin {

  function htmlHead() {
    print Smart::fetch('plugins/typewriter/typewriterHead.tpl');
  }

  function afterBody() {
    print Smart::fetch('plugins/typewriter/typewriterBody.tpl');
  }

  function cssJsSmarty() {
    Smart::addPluginCss('typewriter/run.css');
    Smart::addPluginJs('typewriter/typewriter.js');
  }
}

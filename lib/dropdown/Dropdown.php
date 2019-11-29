<?php
/**
 * BaseClass for different types of Dropdowns
 * serving back in $vars the overwritten calling_class::DEFAULTS
 *
 * @param array $args
 */

abstract class Dropdown {
  public $vars;

  function __construct($args) {
    $class = get_called_class();

    $this->vars = $class::DEFAULTS;
    foreach ($args as $key => $value) {
      $this->vars[$key] = $value;
    }
  }
}

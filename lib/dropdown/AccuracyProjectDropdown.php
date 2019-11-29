<?php
/**
 * {include "bits/projectDropdown.tpl" id="<someUniqueID>"}
 * projectDropdown.tpl is displaying project visibility
 *
 * @param array expects array of overwritten DEFAULTS
 * @return object
 */

class AccuracyProjectDropdown extends Dropdown {
  public $resultSet = null;

  const DEFAULTS = [
    'id' => 'accuracyProjectDropdown',
    'name' => 'visibility',
    'selectedValue' => '',
    'disabled' => false,
  ];

  function __construct($args) {
      parent::__construct($args);
      $this->resultSet  = AccuracyProject::VIS_NAMES;

      return $this;
  }

}

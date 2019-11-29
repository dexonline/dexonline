<?php
/**
 * {include "bits/statusDropdown.tpl" id="<someUniqueID>"}
 * statusDropdown.tpl is displaying current definition status
 *
 * @param mixed expects array of overwritten DEFAULTS
 * @return array
 */

class DefinitionStatusDropdown extends Dropdown {
  public $resultSet = null;

  const DEFAULTS = [
    'id' => 'definitionStatusDropdown',
    'name' => 'status',
    'anyOption' => false,
    'selectedValue' => '',
    'disabled' => false,
  ];

  function __construct($args) {
      parent::__construct($args);
      $this->resultSet  = Definition::STATUS_NAMES;

      return $this;
  }

}

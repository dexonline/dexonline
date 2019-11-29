<?php
/**
 * Each dropdown must have a unique ID.
 * Use default value "id" if only one of this is down the template chain.
 * Otherwise pass a unique ID each time from parent template.
 * {include "bits/modelTypeDropdown.tpl" id="<someUniqueID>"}
 *
 * @param  function expects "getAll", "loadCanonical" or other defined functions in ModelType model class
 * @param  mixed expects array of overwritten DEFAULTS
 * @return object
 */

class ModelTypeDropdown extends Dropdown {
  public $resultSet = null;

  const DEFAULTS = [
    'id' => null,
    'name' => 'modelType',
    'selectedValue' => null,
    'submitValue' => 'code',
    'multiple' => false,
  ];

  function __construct($function, $args) {
      parent::__construct($args);
      $this->resultSet  = ModelType::$function();

      return $this;
  }

}

<?php
/**
 * Each dropdown must have a unique ID.
 * Use default value "id" if only one of this is down the template chain.
 * Otherwise pass a unique ID each time from parent template.
 * {include "bits/modelNumberDropdown.tpl"}
 * values for modelNumber will be fetched through ajax, based on modelType
 *
 * @param  function expects "loadByType" or other defined functions in FlexModel class
 * @param  string expects modelType
 * @param  array expects array of overwritten DEFAULTS
 * @return object
 */

class ModelNumberDropdown extends Dropdown {
  public $resultSet = null;

  const DEFAULTS = [
    'id' => null,
    'name' => 'modelNumber',
    'submitValue' => 'number',
    'selectedValue' => '',
    'compoundLexeme' => false,
    'allOption' => '',
  ];

  function __construct($function, $modelType, $args) {
      parent::__construct($args);
      $this->resultSet  = FlexModel::$function($modelType);

      return $this;
  }

}

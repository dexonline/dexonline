<?php
/**
 * Each dropdown must have a unique ID.
 * Use default value "id" if only one of this is down the template chain.
 * Otherwise pass a unique ID each time from parent template.
 * {include "bits/sourceDropdown.tpl" id="<someUniqueID>"}
 *
 * @param  function expects "getAll", "getAllCanModerate" or other defined functions in Source model class
 * @param  mixed    expects array of overwritten DEFAULTS
 * @return object
 */

class SourceDropdown extends Dropdown {
  public $resultSet = null;

  const DEFAULTS = [
    'id' => 'sourceDropdown',
    'name' => 'source',
    'selectedValue' => null,
    'submitValue' => 'id',
    'skipAnySource' => false,
    'width' => '100%',
    'autosubmit' => false,
  ];

  function __construct($function, $args) {
      parent::__construct($args);
      $this->resultSet  = Source::$function(Source::SORT_SEARCH);

      return $this;
  }

}

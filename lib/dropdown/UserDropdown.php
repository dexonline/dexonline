<?php
/**
 * Each dropdown must have a unique ID.
 * Use default value "id" if only one of this is down the template chain.
 * Otherwise pass a unique ID each time from parent template.
 * {include "bits/sourceDropdown.tpl" id="<someUniqueID>"}
 *
 * @param  function expects "getModerators" or other defined functions in User model class
 * @param  mixed    expects array of overwritten DEFAULTS
 * @return object
 */

class UserDropdown extends Dropdown {
  public $resultSet = null;

  const DEFAULTS = [
    'name' => 'users',
    'id' => 'userDropdown',
    'submitValue' => null,
    'selectedValue' => null,
  ];

  function __construct($function, $args) {
    parent::__construct($args);
    $this->resultSet  = User::$function();

    return $this;
  }
}

<?php

class VisualElFinder extends elFinder {
	public function __construct($opts) {
    parent::__construct($opts);
    $this->commands['mama'] = 'tata';
    error_log(var_export($this->commands, true));
  }
}

?>

<?php

class ParadigmException extends Exception {
  protected $inflectionId;

  public function __construct($inflectionId, $message, Exception $previous = null) {
    $i = Inflection::get_by_id($inflectionId);
    $msg = "Eroare la construirea formei [{$i->description}]: {$message}";
    parent::__construct($msg, $inflectionId, $previous);
  }

}

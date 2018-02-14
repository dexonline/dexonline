<?php

require_once("../../phplib/Core.php");

$d = Model::factory('Definition')->create();
$d->internalRep = Request::get('internalRep');
$d->sourceId = Request::get('sourceId');
$d->process(false); // no errors/warnings here

echo $d->htmlRep;

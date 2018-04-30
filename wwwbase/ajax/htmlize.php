<?php

require_once("../../phplib/Core.php");

$d = Model::factory('Definition')->create();
$d->internalRep = Request::get('internalRep');
$d->sourceId = Request::get('sourceId');
$d->process();
$d->htmlize();

echo $d->getHtml();

<?php

require_once '../../lib/Core.php';

$text = Request::get('text');
$definitionId = Request::get('definitionId');

if ($text && $definitionId) {
  $user = User::getActive();

  $typo = Model::factory('Typo')->create();
  $typo->definitionId = $definitionId;
  $typo->problem = $text;
  $typo->userName = $user ? $user->nick : 'Anonim';
  $typo->save();
}

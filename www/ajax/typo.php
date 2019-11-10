<?php

require_once '../../lib/Core.php';

$text = Request::get('text');
$definitionId = Request::get('definitionId');
$success = false;

if ($text && $definitionId) {
  $user = User::getActive();

  $typo = Model::factory('Typo')->create();
  $typo->definitionId = $definitionId;
  $typo->problem = $text;
  $typo->userName = $user ? $user->nick : 'Anonim';
  $success = $typo->save();
  $typo->save();
}

$response = [ 'success' => $success, ];

header('Content-Type: application/json');
print json_encode($response);

<?php
/**
 * Sends restriction menu, used mainly in lexeme/edit
 **/
require_once '../../lib/Core.php';
User::mustHave(User::PRIV_EDIT);

$modelType = Request::get('modelType');
$selectedValues = Request::get('selectedValues');

$restrictions = Constraint::getForModelType($modelType);

foreach ($restrictions as $r) {
  $pos = strpos($selectedValues, $r->code);
  $r->selected = $pos !== false;
}

Smart::assign( [
  'searchResults' => $restrictions,
]);

$output = Smart::fetch('bits/restrictionMenu.tpl');
$debug = Smart::fetch('bits/debugInfoAjax.tpl');

$results = [
  'html'=> $output,
  'debug' => $debug,
];

header('Content-Type: application/json');
print json_encode($results);

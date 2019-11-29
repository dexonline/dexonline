<?php
/**
 * Sends a form for editing Models
 **/
require_once '../../lib/Core.php';
User::mustHave(User::PRIV_EDIT);

/* first time loading, so selectedValue for modelType will be the default 'A' */
$modelTypes = new ModelTypeDropdown('loadCanonical', [ 'selectedValue' => 'A' ]);
$modelNumbers = new ModelNumberDropdown('loadByType', $modelTypes->vars['selectedValue'], []);

Smart::assign( [
  'modelTypes' => (array)$modelTypes,
  'modelNumbers' => (array)$modelNumbers,
  'mergeToolLink' => Router::link('lexeme/mergeTool'),
  'bulkLabelSelectSuffixLink' => Router::link('lexeme/bulkLabelSelectSuffix'),
]);

$output = Smart::fetch('bits/dashboardFlexModels.tpl');

echo $output;

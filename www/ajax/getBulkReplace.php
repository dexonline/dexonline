<?php
/**
 * Sends a form for bulk-replace search
 **/
require_once '../../lib/Core.php';
User::mustHave(User::PRIV_EDIT);

$sources = new SourceDropdown('getAll', []);

Smart::assign( [
  'sources' => (array)$sources,
  'bulkReplaceLink' => Router::link('aggregate/bulkReplace'),
]);

$output = Smart::fetch('bits/bulkReplace.tpl');

echo $output;

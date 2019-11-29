<?php
/**
 * Translates a sourceId + word into a volume + page + image URL.
 **/
require_once '../../lib/Core.php';
User::mustHave(User::PRIV_EDIT | User::PRIV_TRAINEE);

$sources = new SourceDropdown('getSourcesWithPageImages', [ 'skipAnySource' => true, 'width' => '300px' ]);
Smart::assign('sources', (array)$sources);

$output = Smart::fetch('bits/pageModalContent.tpl');

echo $output;

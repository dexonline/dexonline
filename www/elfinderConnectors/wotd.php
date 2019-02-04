<?php

require_once '../../lib/Core.php';
require_once '../../lib/third-party/elfinder/autoload.php';

$opts = ElfinderUtil::getOptions('img/wotd/', 'Imagini cuvântul zilei');

// run elFinder
$connector = new elFinderConnector(new elFinder($opts));
$connector->run();

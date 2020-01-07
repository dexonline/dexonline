<?php

require_once __DIR__ . '/../lib/Core.php';

$options = getopt('', ['force']);
$force = array_key_exists('force', $options);

SyncWiki::process($force) || exit(1);

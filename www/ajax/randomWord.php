<?php

/**
 * Serve a random sample of normative words. The client should consume this
 * sample, one per index page load, then call us again for another
 * sample. Since our output could be cached, serve a large sample so that
 *   (1) the client doesn't receive the same sample again and
 *   (2) not too many clients will see the same words.
 */

require_once '../../lib/Core.php';

const FILE_NAME = Config::STATIC_PATH . 'download/normative-words.txt';
const COMMAND = 'shuf %s | head -n 1000';

$cmd = sprintf(COMMAND, FILE_NAME);

OS::execute($cmd, $output);

header('Content-type: text/plain');
print $output;

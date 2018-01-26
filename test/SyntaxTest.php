<?php

class SyntaxTest extends PHPUnit_Framework_TestCase {
    public function testPhpSyntax()
    {
        $php = PHP_BINARY;
        $folders = array('wwwbase', 'phplib', 'tools');
        $errors = [];

        foreach ($folders as $dir) {
            $directory = dirname(__DIR__) . "/$dir";
            $dirIt = new RecursiveDirectoryIterator($directory);
            $itIt = new RecursiveIteratorIterator($dirIt);
            $files = new RegexIterator($itIt, '/\.php$/');


            foreach ($files as $file) {
                $summary = exec("$php -l $file", $output, $statusCode);
                if ($statusCode !== 0) {
                    $errors[] = $summary;
                }
            }
        }

        $this->assertCount(0, $errors, implode("\n", $errors));
    }
}

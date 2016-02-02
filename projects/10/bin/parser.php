#!/usr/bin/env php
<?php

set_include_path(dirname(__DIR__) . PATH_SEPARATOR . get_include_path());

require 'vendor/autoload.php';

use JackCompiler\CompilationEngine;

if (empty($argv[1])) {
    die('usage: ' . $argv[0] . ' source.jack [output.xml]' . PHP_EOL);
}

$output = !empty($argv[2]) ? fopen($argv[2], 'w') : STDOUT;
$parser = new CompilationEngine($argv[1], $output);
$parser->compileClass();
$parser->toXML();
fclose($output);
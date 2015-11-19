<?php

require 'vendor/autoload.php';

use JackCompiler\JackTokenizer;
use JackCompiler\CompilationEngine;

if (empty($argv[1])) {
    die('usage: ' . $argv[0] . ' source.jack [output.xml]' . PHP_EOL);
}

$jt = new JackTokenizer($argv[1]);
$output = !empty($argv[2]) ? $argv[2] : 'php://stdout';
$parser = new CompilationEngine($jt, $output);
$parser->compileClass();

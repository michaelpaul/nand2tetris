#!/usr/bin/env php
<?php

set_include_path(dirname(__DIR__) . PATH_SEPARATOR . get_include_path());

require 'vendor/autoload.php';

use JackCompiler\Main;

if (in_array('-h', $argv) || in_array('--help', $argv)) {
    echo("usage:  JackCompiler.php\t\t - compiles all .jack files in current directory" . PHP_EOL);
    echo("\tJackCompiler.php Directory\t - compiles all .jack in Directory" . PHP_EOL);
    echo("\tJackCompiler.php Source.jack\t - compiles Source.jack to Source.vm" . PHP_EOL);
} else {
    $input = $argv[1];
    $dumpAst = false;
    if ('--ast' == $input) {
        $dumpAst = true;
        $input = $argv[2];
    }
    $compiler = new Main($input);
    $compiler->compile($dumpAst);
}
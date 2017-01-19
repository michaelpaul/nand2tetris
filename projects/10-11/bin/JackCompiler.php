#!/usr/bin/env php
<?php

set_include_path(dirname(__DIR__) . PATH_SEPARATOR . get_include_path());

require 'vendor/autoload.php';

use JackCompiler\Main;

if (in_array('-h', $argv) || in_array('--help', $argv)) {
    echo("usage:  JackCompiler.php [--ast]\t\t - compiles all .jack files in current directory" . PHP_EOL);
    echo("\tJackCompiler.php [--ast] Directory\t - compiles all .jack in Directory" . PHP_EOL);
    echo("\tJackCompiler.php [--ast] Source.jack\t - compiles Source.jack to Source.vm" . PHP_EOL);
} else {
    $input = null;
    $ast = false;
    
    if ($argc == 2) {
        if ($argv[1] == '--ast') {
            $ast = true;
        } else {
            $input = $argv[1];
        }
    } else if ($argc == 3) {
        if ($argv[1] == '--ast') {
            $ast = true;
        }
        $input = $argv[2];
    }

    $compiler = new Main($input);
    $compiler->compile($ast);
}
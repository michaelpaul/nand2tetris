#!/usr/bin/env php
<?php

error_reporting(E_ALL | E_STRICT);

require_once __DIR__ . '/vendor/autoload.php';

use Assembler\Main;

if (empty($argv[1])) {
    die("usage: $argv[0] file.asm \n");
}

$main = new Main;
$main->assemble($argv[1]);

#!/usr/bin/env php
<?php

error_reporting(E_ALL | E_STRICT);

require_once __DIR__ . '/vendor/autoload.php';

use VMTranslator\Main;

if (empty($argv[1])) {
    die("usage: $argv[0] [file.vm | dir] \n");
}

$main = new Main;
$main->translate($argv[1]);

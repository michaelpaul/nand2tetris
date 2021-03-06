#!/usr/bin/env php
<?php

error_reporting(E_ALL | E_STRICT);

require_once __DIR__ . '/vendor/autoload.php';

use VMTranslator\Main;

if (empty($argv[1])) {
    die("usage: $argv[0] [--nobootstrap] [-o outputfile] file.vm | dir \n");
}

$options = getopt("o:", array(
    'nobootstrap'
));

$inputFile = end($argv);
$main = new Main;
if (isset($options['o'])) {
    $main->setOutputFilename($options['o']);
}
if (isset($options['nobootstrap'])) {
    $main->setBootstrap(false);
}
$main->translate($inputFile);

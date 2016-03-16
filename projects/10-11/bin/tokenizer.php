#!/usr/bin/env php
<?php

set_include_path(dirname(__DIR__) . PATH_SEPARATOR . get_include_path());

require 'vendor/autoload.php';

use JackCompiler\JackTokenizer;

if (empty($argv[1])) {
    die('usage: ' . $argv[0] . ' source.jack [output.xml]' . PHP_EOL);
}

$jt = new JackTokenizer($argv[1]);
$output = !empty($argv[2]) ? $argv[2] : 'php://stdout';

$doc = new DOMDocument();
$doc->formatOutput = true;
$tokens = $doc->createElement('tokens');
$doc->appendChild($tokens);

while ($jt->hasMoreTokens()) {
    $jt->advance();
    switch ($jt->tokenType()) {
        case JackTokenizer::KEYWORD:
            $token = $jt->keywordToken();
            break;
        case JackTokenizer::SYMBOL:
            $token = $jt->symbolToken();
            break;
        case JackTokenizer::IDENTIFIER:
            $token = $jt->identifierToken();
            break;
        case JackTokenizer::INT_CONST:
            $token = $jt->intValToken();
            break;
        case JackTokenizer::STRING_CONST:
            $token = $jt->stringValToken();
            break;
    }
    $tokens->appendChild($doc->createElement($token->type, $token->val));
}

$doc->save($output);

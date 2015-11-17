<?php

require 'vendor/autoload.php';

use JackCompiler\JackTokenizer;
$jt = new JackTokenizer($argv[1]);
$output = $argv[2];
// $output = 'php://stdout';

$doc = new DOMDocument();
$doc->formatOutput = true;
$tokens = $doc->createElement('tokens');
$doc->appendChild($tokens);

while($jt->hasMoreTokens()) {
    $jt->advance();
    $tokenType = $tokenVal = null;
    switch ($jt->tokenType()) {
        case JackTokenizer::KEYWORD:
            $tokenType = 'keyword';
            $tokenVal = $jt->keyword();
            break;
        case JackTokenizer::SYMBOL:
            $tokenType = 'symbol';
            $tokenVal = $jt->symbol();
            break;
        case JackTokenizer::IDENTIFIER:
            $tokenType = 'identifier';
            $tokenVal = $jt->identifier();
            break;
        case JackTokenizer::INT_CONST:
            $tokenType = 'integerConstant';
            $tokenVal = $jt->stringVal();
            break;
        case JackTokenizer::STRING_CONST:
            $tokenType = 'string';
            $tokenVal = $jt->stringVal();
            break;
    }
    if (!is_null($tokenType) && !is_null($tokenVal)) {
        $tokens->appendChild($doc->createElement($tokenType, $tokenVal));
    }
}

$doc->save($output);

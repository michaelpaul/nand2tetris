<?php

require 'vendor/autoload.php';

use JackCompiler\JackTokenizer;

if (empty($argv[1])) {
    die('usage: ' . $argv[0] . ' source.jack [output.xml]');
}

$jt = new JackTokenizer($argv[1]);
$output = !empty($argv[2]) ? $argv[2] : 'php://stdout';

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
            $tokenVal = htmlspecialchars($jt->symbol(), ENT_XML1);
            break;
        case JackTokenizer::IDENTIFIER:
            $tokenType = 'identifier';
            $tokenVal = $jt->identifier();
            break;
        case JackTokenizer::INT_CONST:
            $tokenType = 'integerConstant';
            $tokenVal = $jt->intVal();
            break;
        case JackTokenizer::STRING_CONST:
            $tokenType = 'stringConstant';
            $tokenVal = $jt->stringVal();
            break;
        default:
            throw new Exception('Tipo desconhecido de token');    
    }
    $tokens->appendChild($doc->createElement($tokenType, $tokenVal));
}

$doc->save($output);

<?php

namespace JackTests;

use JackCompiler\CompilationEngine;

class CompilerTestCase extends \PHPUnit_Framework_TestCase
{
    protected $input;
    protected $output;
    protected $parser;

    protected function setUp()
    {
        $this->input = fopen('php://memory', 'w');
        $this->output = fopen('php://memory', 'w');
        $this->parser = new CompilationEngine($this->input, $this->output);
    }

    protected function tearDown()
    {
        fclose($this->input);
        fclose($this->output);
    }

    protected function writeTestProgram($text)
    {
        fwrite($this->input, $text);
        rewind($this->input);
    }

    protected function loadXML($string)
    {
        $doc = new \DOMDocument;
        $doc->formatOutput = true;
        $doc->preserveWhiteSpace = false;
        $doc->loadXML($string);
        return $doc;
    }
    
    protected function assertASTEquals($expectedXml)
    {
        $tree = $this->loadXML($expectedXml);
        // $this->assertEqualXMLStructure($tree->firstChild, $this->parser->getCtx());
        $this->assertEquals($tree->firstChild, $this->parser->getCtx());
    }
}

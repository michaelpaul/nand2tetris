<?php

namespace JackTests;

use JackCompiler\CompilationEngine;
use JackCompiler\VMWriter;
use JackCompiler\SymbolTable;

class CompilerTestCase extends \PHPUnit_Framework_TestCase
{
    protected $input;
    protected $parser;

    protected function setUp()
    {
        $this->input = fopen('php://memory', 'w');
        $this->parser = new CompilationEngine($this->input);
        $st = new SymbolTable();
        $st->define('i', 'int', 'var');
        $st->define('x', 'int', 'var');
        $st->define('y', 'int', 'var');
        $st->define('z', 'int', 'var');
        $this->parser->setSymbolTable($st);
        $this->parser->setWriter($this->createMock(VMWriter::class));
    }

    protected function tearDown()
    {
        fclose($this->input);
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

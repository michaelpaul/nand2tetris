<?php

use JackCompiler\CompilationEngine;

class CompilationEngineTest extends PHPUnit_Framework_TestCase
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
        $doc = new DOMDocument;
        $doc->formatOutput = true;
        $doc->preserveWhiteSpace = false;
        $doc->loadXML($string);
        return $doc;
    }

    public function testEmptyClass()
    {
        $this->writeTestProgram('class Square {}');

        $this->parser->compileClass();

        $expected = $this->loadXML('
            <class>
                <keyword>class</keyword>
                <identifier>Square</identifier>
                <symbol>{</symbol>
                <symbol>}</symbol>
            </class>
        ');

        $this->assertEqualXMLStructure($expected->firstChild, $this->parser->getCtx());
        $this->assertEquals($expected->firstChild, $this->parser->getCtx());
    }

    public function testCompileClassVarDec()
    {
        $this->writeTestProgram('field int size;');

        $this->parser->advance();
        $this->parser->compileClassVarDec();

        $expected = $this->loadXML('
            <classVarDec>
              <keyword>field</keyword>
              <keyword>int</keyword>
              <identifier>size</identifier>
              <symbol>;</symbol>
            </classVarDec>
        ');
        $this->assertEquals($expected->firstChild, $this->parser->getCtx());
    }

    public function testCompileStaticClassVarDec()
    {
        $this->writeTestProgram('static char Drive;');

        $this->parser->advance();
        $this->parser->compileClassVarDec();

        $expected = $this->loadXML('
            <classVarDec>
              <keyword>static</keyword>
              <keyword>char</keyword>
              <identifier>Drive</identifier>
              <symbol>;</symbol>
            </classVarDec>
        ');
        $this->assertEquals($expected->firstChild, $this->parser->getCtx());
    }

    public function testMultiVarClassVarDec()
    {
        $this->writeTestProgram('field int x, y, z;');

        $this->parser->advance();
        $this->parser->compileClassVarDec();

        $expected = $this->loadXML('
            <classVarDec>
              <keyword>field</keyword>
              <keyword>int</keyword>
              <identifier>x</identifier>
              <symbol>,</symbol>
              <identifier>y</identifier>
              <symbol>,</symbol>
              <identifier>z</identifier>
              <symbol>;</symbol>
            </classVarDec>
        ');
        $this->assertEquals($expected->firstChild, $this->parser->getCtx());
    }

    public function testCompileEmptyConstructor()
    {
        $this->writeTestProgram('constructor Square new() {}');

        $this->parser->advance();
        $this->parser->compileSubroutine();

        $expected = $this->loadXML('
            <subroutineDec>
              <keyword>constructor</keyword>
              <identifier>Square</identifier>
              <identifier>new</identifier>
              <symbol>(</symbol>
              <parameterList />
              <symbol>)</symbol>
              <subroutineBody>
                <symbol>{</symbol>
                <symbol>}</symbol>
              </subroutineBody>
            </subroutineDec>
        ');
        $this->assertEquals($expected->firstChild, $this->parser->getCtx());
    }

    public function testEmptyParameterList()
    {
        $this->writeTestProgram(' /* int empty, char parameterList */ ');

        $this->parser->advance();
        $this->parser->compileParameterList();

        $expected = $this->loadXML('<parameterList />');
        $this->assertEquals($expected->firstChild, $this->parser->getCtx());
    }

    public function testSingleParameter()
    {
        $this->writeTestProgram('CodeGenerator cg');

        $this->parser->advance();
        $this->parser->compileParameterList();

        $expected = $this->loadXML('
            <parameterList>
                <identifier>CodeGenerator</identifier>
                <identifier>cg</identifier>
            </parameterList>
        ');
        $this->assertEquals($expected->firstChild, $this->parser->getCtx());
    }

    public function testParameterList()
    {
        $this->writeTestProgram('int x, char wc, Output file');

        $this->parser->advance();
        $this->parser->compileParameterList();

        $expected = $this->loadXML('
            <parameterList>
                <keyword>int</keyword>
                <identifier>x</identifier>
                <symbol>,</symbol>
                <keyword>char</keyword>
                <identifier>wc</identifier>
                <symbol>,</symbol>
                <identifier>Output</identifier>
                <identifier>file</identifier>
            </parameterList>
        ');
        $this->assertEquals($expected->firstChild, $this->parser->getCtx());
    }
}

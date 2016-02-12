<?php

namespace JackTests;

class ProgramStructureTest extends CompilerTestCase
{
    /**
     * @expectedException JackCompiler\ParserError
     * @expectedExceptionMessage Esperava keyword ( 'class' )
     */
    public function testRequiredKeyword()
    {
        $this->parser->compileTerminalKeyword('class');
    }
    
    /**
     * @expectedException JackCompiler\ParserError
     * @expectedExceptionMessage Esperava keyword ( 'constructor' | 'function' | 'method' )
     */
    public function testRequiredKeywordList()
    {
        $this->parser->compileTerminalKeyword('constructor', 'function', 'method');
    }
    
    /**
     * @expectedException JackCompiler\ParserError
     * @expectedExceptionMessage Esperava simbolo ( '{' )
     */
    public function testRequiredSymbol()
    {
        $this->parser->compileTerminalSymbol('{');
    }
    
    /**
     * @expectedException JackCompiler\ParserError
     * @expectedExceptionMessage Esperava simbolo ( ',' | ';' | '[' )
     */
    public function testRequiredSymbolList()
    {
        $this->parser->compileTerminalSymbol(',', ';', '[');
    }
    
    public function testEmptyClass()
    {
        $this->writeTestProgram('class Square {}');

        $this->parser->compileClass();

        $expected = '
            <class>
                <keyword>class</keyword>
                <identifier>Square</identifier>
                <symbol>{</symbol>
                <symbol>}</symbol>
            </class>
        ';

        // $this->assertEqualXMLStructure($expected->firstChild, $this->parser->getCtx());
        $this->assertASTEquals($expected);
    }

    public function testCompileClassVarDec()
    {
        $this->writeTestProgram('field int size;');

        $this->parser->advance();
        $this->parser->compileClassVarDec();

        $expected = '
            <classVarDec>
              <keyword>field</keyword>
              <keyword>int</keyword>
              <identifier>size</identifier>
              <symbol>;</symbol>
            </classVarDec>
        ';
        $this->assertASTEquals($expected);
    }

    public function testCompileStaticClassVarDec()
    {
        $this->writeTestProgram('static char Drive;');

        $this->parser->advance();
        $this->parser->compileClassVarDec();

        $expected = '
            <classVarDec>
              <keyword>static</keyword>
              <keyword>char</keyword>
              <identifier>Drive</identifier>
              <symbol>;</symbol>
            </classVarDec>
        ';
        $this->assertASTEquals($expected);
    }

    public function testMultiVarClassVarDec()
    {
        $this->writeTestProgram('field int x, y, z;');

        $this->parser->advance();
        $this->parser->compileClassVarDec();

        $expected = '
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
        ';
        $this->assertASTEquals($expected);
    }

    public function testCompileEmptyConstructor()
    {
        $this->writeTestProgram('constructor Square new() {}');

        $this->parser->advance();
        $this->parser->compileSubroutine();

        $expected = '
            <subroutineDec>
              <keyword>constructor</keyword>
              <identifier>Square</identifier>
              <identifier>new</identifier>
              <symbol>(</symbol>
              <parameterList />
              <symbol>)</symbol>
              <subroutineBody>
                <symbol>{</symbol>
                <statements />
                <symbol>}</symbol>
              </subroutineBody>
            </subroutineDec>
        ';
        $this->assertASTEquals($expected);
    }

    public function testCompileFunction()
    {
        $this->writeTestProgram('function void Render() {}');

        $this->parser->advance();
        $this->parser->compileSubroutine();

        $expected = '
            <subroutineDec>
              <keyword>function</keyword>
              <keyword>void</keyword>
              <identifier>Render</identifier>
              <symbol>(</symbol>
              <parameterList />
              <symbol>)</symbol>
              <subroutineBody>
                <symbol>{</symbol>
                <statements />
                <symbol>}</symbol>
              </subroutineBody>
            </subroutineDec>
        ';
        $this->assertASTEquals($expected);
    }
    
    public function testCompileMethod()
    {
        $this->writeTestProgram('method boolean getSize() {}');

        $this->parser->advance();
        $this->parser->compileSubroutine();

        $expected = '
            <subroutineDec>
              <keyword>method</keyword>
              <keyword>boolean</keyword>
              <identifier>getSize</identifier>
              <symbol>(</symbol>
              <parameterList />
              <symbol>)</symbol>
              <subroutineBody>
                <symbol>{</symbol>
                <statements />
                <symbol>}</symbol>
              </subroutineBody>
            </subroutineDec>
        ';
        $this->assertASTEquals($expected);
    }
    
    public function testEmptyParameterList()
    {
        $this->writeTestProgram(' /* int empty, char parameterList */ ');

        $this->parser->advance();
        $this->parser->compileParameterList();

        $expected = '<parameterList />';
        $this->assertASTEquals($expected);
    }

    public function testSingleParameter()
    {
        $this->writeTestProgram('CodeGenerator cg');

        $this->parser->advance();
        $this->parser->compileParameterList();

        $expected = '
            <parameterList>
                <identifier>CodeGenerator</identifier>
                <identifier>cg</identifier>
            </parameterList>
        ';
        $this->assertASTEquals($expected);
    }

    public function testParameterList()
    {
        $this->writeTestProgram('int x, char wc, Output file');

        $this->parser->advance();
        $this->parser->compileParameterList();

        $expected = '
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
        ';
        $this->assertASTEquals($expected);
    }
    
    public function testSingleVarDec()
    {
        $this->writeTestProgram('var int score;');

        $this->parser->advance();
        $this->parser->compileVarDec();

        $expected = '
            <varDec>
              <keyword>var</keyword>
              <keyword>int</keyword>
              <identifier>score</identifier>
              <symbol>;</symbol>
            </varDec>
        ';
        $this->assertASTEquals($expected);
    }
    
    public function testMultiVarDec()
    {
        $this->writeTestProgram('var List tasks, jobs, workers;');

        $this->parser->advance();
        $this->parser->compileVarDec();

        $expected = '
            <varDec>
              <keyword>var</keyword>
              <identifier>List</identifier>
              <identifier>tasks</identifier>
              <symbol>,</symbol>
              <identifier>jobs</identifier>
              <symbol>,</symbol>
              <identifier>workers</identifier>
              <symbol>;</symbol>
            </varDec>
        ';
        $this->assertASTEquals($expected);
    }
}

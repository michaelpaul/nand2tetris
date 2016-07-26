<?php

namespace JackTests;

use JackCompiler\SymbolTable;

class SymbolTableTest extends CompilerTestCase
{
    public function testScopes()
    {
        $st = new SymbolTable();
        // class
        $st->define('seed', 'int', 'static');
        $st->define('board', 'Board', 'field');
        $st->define('score_title', 'string', 'field');
        
        $this->assertEquals(1, $st->varCount('static'));
        $this->assertEquals(2, $st->varCount('field'));
        $this->assertSame(0, $st->indexOf('seed'));
        $this->assertSame(0, $st->indexOf('board'));
        $this->assertSame(1, $st->indexOf('score_title'));
        
        // subroutine
        $st->startSubroutine();
        $st->define('Illidan', 'char', 'var');
        $st->define('Zeratul', 'char', 'var');
        
        $this->assertEquals(2, $st->varCount('var'));
        $this->assertSame(1, $st->indexOf('Zeratul'));
        
        // subroutine
        $st->startSubroutine();
        $st->define('CPU', 'Unit', 'var');
        $st->define('GPU', 'Unit', 'var');
        $st->define('system', 'type', 'var');
        
        $this->assertEquals(3, $st->varCount('var'));
        $this->assertFalse($st->contains('Illidan'));
        $this->assertSame(0, $st->indexOf('CPU'));
        $this->assertSame(2, $st->indexOf('system'));
    }
    
    public function varDecProvider()
    {
        return [
            ['i', 'int', 0],
            ['path', 'String', 1],
            ['fail', 'boolean', 2],
            ['whale', 'boolean', 3],
        ];
    }
    
    /**
     * @dataProvider varDecProvider
     */
    public function testCompileVarDec($name, $type, $index)
    {
        $this->writeTestProgram("{ var int i; var String path; var boolean fail, whale; }");
        $this->parser->setSymbolTable(new SymbolTable);
        $this->parser->advance();
        $this->parser->compileSubroutineBody('fakeMethod');
        
        $st = $this->parser->getSymbolTable();
        
        $this->assertTrue($st->contains($name));
        $this->assertInstanceOf('JackCompiler\Symbol', $st->get($name));
        $this->assertEquals('var', $st->kindOf($name));
        $this->assertEquals($type, $st->typeOf($name));
        $this->assertSame($index, $st->indexOf($name));
    }
    
    /**
     * @expectedException Exception
     * @expectedExceptionMessage símbolo "seed" duplicado
     */
    public function testDuplicate()
    {
        $st = new SymbolTable();
        // class scope
        $st->define('seed', 'int', 'static');
        $st->define('seed', 'int', 'static');
    }
    
    /**
     * @expectedException Exception
     * @expectedExceptionMessage Identificador não encontrado: whereAmI
     */
    public function testNotFound()
    {
        $st = new SymbolTable();
        $st->get('whereAmI');
    }
}

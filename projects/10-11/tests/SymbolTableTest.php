<?php

namespace JackTests;

use JackCompiler\SymbolTable;

class SymbolTableTest extends CompilerTestCase
{
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
    public function testVarDec($name, $type, $index)
    {
        $this->writeTestProgram("{ var int i; var String path; var boolean fail, whale; }");
        $this->parser->advance();
        $this->parser->compileSubroutineBody();
        
        $st = $this->parser->getSymbolTable();
        
        $this->assertTrue($st->contains($name));
        $symbol = $st->get($name);
        
        $this->assertEquals($name, $symbol->name);
        $this->assertEquals($type, $symbol->type);
        $this->assertEquals('var', $symbol->kind);
        $this->assertEquals($index, $symbol->index);
    }
}
<?php

namespace JackTests;

class StatementsTest extends CompilerTestCase
{
    public function testLet()
    {
        $this->writeTestProgram('let game = x;');
        $this->parser->advance();
        $this->parser->compileLet();

        $expected = '
            <letStatement>
                <keyword>let</keyword>
                <identifier>game</identifier>
                <symbol>=</symbol>
                <expression>
                  <term>
                    <identifier>x</identifier>
                  </term>
                </expression>
                <symbol>;</symbol>
            </letStatement>
        ';

        $this->assertASTEquals($expected);
    }
    
    public function testLetArray()
    {
        $this->writeTestProgram('let vector[x] = sector;');
        $this->parser->advance();
        $this->parser->compileLet();

        $expected = '
            <letStatement>
                <keyword>let</keyword>
                <identifier>vector</identifier>
                <symbol>[</symbol>
                <expression>
                  <term>
                    <identifier>x</identifier>
                  </term>
                </expression>
                <symbol>]</symbol>
                <symbol>=</symbol>
                <expression>
                  <term>
                    <identifier>sector</identifier>
                  </term>
                </expression>
                <symbol>;</symbol>
            </letStatement>
        ';

        $this->assertASTEquals($expected);
    }
    
    public function testSimpleIf()
    {
        $this->writeTestProgram('if (x) { let x = y; let a = b; }');
        $this->parser->advance();
        $this->parser->compileIf();
        $ifStatement = simplexml_import_dom($this->parser->getCtx());
        $this->assertEquals('ifStatement', $ifStatement->getName());
        $this->assertEquals('if', $ifStatement->keyword[0]);
        $this->assertEquals('(', $ifStatement->symbol[0]);
        $this->assertEquals('x', $ifStatement->expression->term->identifier[0]);
        $this->assertEquals(')', $ifStatement->symbol[1]);
        $this->assertEquals('{', $ifStatement->symbol[2]);
        $this->assertEquals(1, $ifStatement->statements->count());
        $this->assertEquals(2, $ifStatement->statements->letStatement->count());
        $this->assertEquals('}', $ifStatement->symbol[3]);
    }
    
    public function testSimpleIfElse()
    {
        $this->writeTestProgram(
            'if (x) { let x = y; } else { let a = b; let z = y; }'
        );
        $this->parser->advance();
        $this->parser->compileIf();
        $ifStatement = simplexml_import_dom($this->parser->getCtx());
        $this->assertEquals('ifStatement', $ifStatement->getName());
        $this->assertEquals('if', $ifStatement->keyword[0]);
        $this->assertEquals('else', $ifStatement->keyword[1]);
        $this->assertEquals(2, $ifStatement->statements->count());
        $this->assertEquals(2, $ifStatement->statements[1]->letStatement->count());
        // deep path
        $y = $ifStatement->statements[1]->letStatement[1]->expression->term->identifier;
        $this->assertEquals('y', $y);
    }
}

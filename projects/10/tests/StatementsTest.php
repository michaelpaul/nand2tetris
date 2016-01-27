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
        $ifStatement = simplexml_import_dom($this->parser->getCtx(), 'SimpleXMLIterator');
        $this->assertEquals('ifStatement', $ifStatement->getName());
        $this->assertEquals('if', $ifStatement->keyword[0]);
        $this->assertEquals('(', $ifStatement->symbol[0]);
        $this->assertEquals('x', $ifStatement->expression->term->identifier[0]);
        $this->assertEquals(')', $ifStatement->symbol[1]);
        $this->assertEquals('{', $ifStatement->symbol[2]);
        $this->assertCount(1, $ifStatement->statements);
        $this->assertCount(2, $ifStatement->statements->letStatement);
        $this->assertEquals('}', $ifStatement->symbol[3]);
    }
    
    public function testSimpleIfElse()
    {
        $this->writeTestProgram(
            'if (x) { let x = y; } else { let a = b; let z = y; }'
        );
        $this->parser->advance();
        $this->parser->compileIf();
        $ifStatement = simplexml_import_dom($this->parser->getCtx(), 'SimpleXMLIterator');
        $this->assertEquals('ifStatement', $ifStatement->getName());
        $this->assertEquals('if', $ifStatement->keyword[0]);
        $this->assertEquals('else', $ifStatement->keyword[1]);
        $this->assertCount(2, $ifStatement->statements);
        $this->assertCount(2, $ifStatement->statements[1]->letStatement);
        // deep path
        $y = $ifStatement->statements[1]->letStatement[1]->expression->term->identifier;
        $this->assertEquals('y', $y);
    }
    
    public function testNestedIf()
    {
        $this->writeTestProgram(
            'if (x) { if (y) { if (z) {  } } }'
        );
        $this->parser->advance();
        $this->parser->compileIf();
        $ifStatement = simplexml_import_dom($this->parser->getCtx(), 'SimpleXMLIterator');
        $this->assertEquals('ifStatement', $ifStatement->getName());
        $this->assertCount(3, $ifStatement->xpath('//ifStatement'));
        $this->assertCount(3, $ifStatement->xpath('//identifier'));
        // deep path
        $z = $ifStatement->statements
            ->ifStatement->statements
            ->ifStatement->expression->term->identifier;
        $this->assertEquals('z', $z);
    }
}

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
    
    public function testWhile()
    {
        $this->writeTestProgram('while (x) { if(y){} let z = z; }');
        $this->parser->advance();
        $this->parser->compileWhile();
        
        $whileStatement = simplexml_import_dom($this->parser->getCtx(), 'SimpleXMLIterator');
        $this->assertEquals('whileStatement', $whileStatement->getName());
        $this->assertEquals('while', $whileStatement->keyword[0]);
        $this->assertEquals('(', $whileStatement->symbol[0]);
        $this->assertEquals('expression', $whileStatement->expression->getName());
        $this->assertEquals(')', $whileStatement->symbol[1]);
        $this->assertEquals('{', $whileStatement->symbol[2]);
        $this->assertEquals('statements', $whileStatement->statements->getName());
        $this->assertCount(2, $whileStatement->statements->children());
        $this->assertCount(1, $whileStatement->statements->ifStatement);
        $this->assertCount(1, $whileStatement->statements->letStatement);
        $this->assertEquals('}', $whileStatement->symbol[3]);
    }
    
    public function testReturnVoid()
    {
        $this->writeTestProgram('return;');
        $this->parser->advance();
        $this->parser->compileReturn();
        
        $returnStatement = simplexml_import_dom($this->parser->getCtx());
        $this->assertEquals('returnStatement', $returnStatement->getName());
        $this->assertEquals('return', $returnStatement->keyword[0]);
        $this->assertEquals(';', $returnStatement->symbol[0]);
    }
    
    public function testReturnExpr()
    {
        $this->writeTestProgram('return x;');
        $this->parser->advance();
        $this->parser->compileReturn();
        
        $returnStatement = simplexml_import_dom($this->parser->getCtx());
        $this->assertEquals('x', $returnStatement->expression->term->identifier);
    }
    
    public function testSimpleExpressionList()
    {
        // empty
        $this->writeTestProgram('');
        $this->parser->advance();
        $this->parser->compileExpressionList();
        $exprList = simplexml_import_dom($this->parser->getCtx(), 'SimpleXMLIterator');
        $this->assertEquals('expressionList', $exprList->getName());
        $this->assertCount(0, $exprList->children());
        
        // single exp
        $this->writeTestProgram('x');
        $this->parser->advance();
        $this->parser->compileExpressionList();
        $exprList = simplexml_import_dom($this->parser->getCtx());
        $this->assertEquals('expressionList', $exprList->getName());
    }
    
    public function testExpressionList()
    {
        $this->writeTestProgram('x, y, z');
        $this->parser->advance();
        $this->parser->compileExpressionList();
        
        $exprList = simplexml_import_dom($this->parser->getCtx(), 'SimpleXMLIterator');
        $this->assertEquals('expressionList', $exprList->getName());
        $this->assertCount(3, $exprList->expression);
        $vars = $exprList->xpath('//identifier');
        $this->assertEquals('x', $vars[0]);
        $this->assertEquals('y', $vars[1]);
        $this->assertEquals('z', $vars[2]);
    }
    
    public function testDo()
    {
        $this->writeTestProgram('do draw();');
        $this->parser->advance();
        $this->parser->compileDo();
        
        $doStatement = simplexml_import_dom($this->parser->getCtx(), 'SimpleXMLIterator');
        $this->assertEquals('doStatement', $doStatement->getName());
        $this->assertEquals('draw', $doStatement->identifier);
        $this->assertCount(3, $doStatement->symbol);
        $this->assertEquals('expressionList', $doStatement->expressionList->getName());
    }
    
    public function testSubroutineCall()
    {
        $this->writeTestProgram('do Output.println();');
        $this->parser->advance();
        $this->parser->compileDo();
        
        $doStatement = simplexml_import_dom($this->parser->getCtx());
        $this->assertEquals('Output', $doStatement->identifier[0]);
        $this->assertEquals('.', $doStatement->symbol[0]);
        $this->assertEquals('println', $doStatement->identifier[1]);
    }
}

<?php

namespace JackTests;

class ExpressionsTest extends CompilerTestCase
{
    public function integerConstantProvider()
    {
        return [[0], [32767]];
    }
    
    /**
     * @dataProvider integerConstantProvider
     */
    public function testIntegerConstant($integer)
    {
        $this->writeTestProgram("$integer");
        $this->parser->advance();
        $this->parser->compileTerm();
        
        $term = $this->parser->toSimpleXML();
        $this->assertEquals('term', $term->getName());
        $this->assertEquals("$integer", $term->integerConstant);
    }
    
    public function testStringConstant()
    {
        $this->writeTestProgram('"The quick brown fox jumps over the lazy dog"');
        $this->parser->advance();
        $this->parser->compileTerm();
        
        $term = $this->parser->toSimpleXML();
        $this->assertEquals('term', $term->getName());
        $this->assertEquals('The quick brown fox jumps over the lazy dog', $term->stringConstant);
    }
    
    public function testKeywordConstant()
    {
        $this->writeTestProgram('true | false & null + this');
        $this->parser->advance();
        $this->parser->compileExpression();
        $expr = $this->parser->toSimpleXML();
        $this->assertEquals('expression', $expr->getName());
        $this->assertEquals('true', $expr->term[0]->keyword);
        $this->assertEquals('|', $expr->symbol[0]);
        $this->assertEquals('false', $expr->term[1]->keyword);
        $this->assertEquals('&', $expr->symbol[1]);
        $this->assertEquals('null', $expr->term[2]->keyword);
        $this->assertEquals('+', $expr->symbol[2]);
        $this->assertEquals('this', $expr->term[3]->keyword);
        $this->assertCount(4, $expr->term);
        $this->assertCount(3, $expr->symbol);
    }
    
    public function testSubscript()
    {
        $this->writeTestProgram('players[i]');
        $this->parser->advance();
        $this->parser->compileTerm();
        $term = $this->parser->toSimpleXML();
        $this->assertEquals('players', $term->identifier[0]);
        $this->assertEquals('[', $term->symbol[0]);
        $this->assertEquals('i', $term->expression->term->identifier);
        $this->assertEquals(']', $term->symbol[1]);
    }
    
    public function testMethodCall()
    {
        $this->writeTestProgram('multiply(x, y)');
        $this->parser->advance();
        $this->parser->compileTerm();
        $term = $this->parser->toSimpleXML();
        $this->assertEquals('multiply', $term->identifier[0]);
        $this->assertEquals('(', $term->symbol[0]);
        $this->assertEquals('x', $term->expressionList->expression[0]->term->identifier);
        $this->assertEquals('y', $term->expressionList->expression[1]->term->identifier);
        $this->assertEquals(')', $term->symbol[1]);
    }
    
    public function testFunctionCall()
    {
        $this->writeTestProgram('z.compileClass(y, x)');
        $this->parser->advance();
        $this->parser->compileTerm();
        $term = $this->parser->toSimpleXML();
        $this->assertEquals('z', $term->identifier[0]);
        $this->assertEquals('.', $term->symbol[0]);
        $this->assertEquals('compileClass', $term->identifier[1]);
        $this->assertEquals('(', $term->symbol[1]);
        $this->assertEquals('y', $term->expressionList->expression[0]->term->identifier);
        $this->assertEquals('x', $term->expressionList->expression[1]->term->identifier);
        $this->assertEquals(')', $term->symbol[2]);
    }
    
    public function testParens()
    {
        $this->writeTestProgram('(x > y)');
        $this->parser->advance();
        $this->parser->compileTerm();
        $term = $this->parser->toSimpleXML();
        $this->assertEquals('(', $term->symbol[0]);
        $this->assertEquals('x', $term->expression->term[0]->identifier);
        $this->assertEquals('>', $term->expression->symbol);
        $this->assertEquals('y', $term->expression->term[1]->identifier);
        $this->assertEquals(')', $term->symbol[1]);
    }
    
    public function testNestedParens()
    {
        $this->writeTestProgram('((x + y) * z)');
        $this->parser->advance();
        $this->parser->compileExpression();
        
        // ( expression )
        $root = $this->parser->toSimpleXML();
        $this->assertCount(3, $root->xpath('//expression'));
        // term * term
        $secondExpr = $root->term->expression;
        // term + term
        $thirdExpr = $secondExpr->term[0]->expression;
        
        $this->assertEquals('(', $root->term->symbol[0]);
        $this->assertEquals('(', $secondExpr->term[0]->symbol[0]);
        $this->assertEquals('x', $thirdExpr->term[0]->identifier);
        $this->assertEquals('+', $thirdExpr->symbol);
        $this->assertEquals('y', $thirdExpr->term[1]->identifier);
        $this->assertEquals(')', $secondExpr->term[0]->symbol[1]);
        $this->assertEquals('*', $secondExpr->symbol);
        $this->assertEquals('z', $secondExpr->term[1]->identifier);
        $this->assertEquals(')', $root->term->symbol[1]);
    }
    
    public function unaryOpProvider()
    {
        return [['-'], ['~']];
    }
    
    /**
     * @dataProvider unaryOpProvider
     */
    public function testUnaryOp($op)
    {
        $this->writeTestProgram($op . '32');
        $this->parser->advance();
        $this->parser->compileTerm();
        $term = $this->parser->toSimpleXML();
        $this->assertEquals($op, $term->symbol);
        $this->assertEquals('32', $term->term->integerConstant);
    }
}

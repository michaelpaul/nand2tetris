<?php

namespace JackTests;

class ExpressionsTest extends CompilerTestCase
{
    public function testMinIntegerConstant()
    {
        $this->writeTestProgram('0');
        $this->parser->advance();
        $this->parser->compileTerm();
        
        $term = $this->parser->toSimpleXML();
        $this->assertEquals('term', $term->getName());
        $this->assertEquals('0', $term->integerConstant);
    }
    
    public function testMaxIntegerConstant()
    {
        $this->writeTestProgram('32767');
        $this->parser->advance();
        $this->parser->compileTerm();
        
        $term = $this->parser->toSimpleXML();
        $this->assertEquals('term', $term->getName());
        $this->assertEquals('32767', $term->integerConstant);
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
        $this->writeTestProgram('JackCompiler.compileClass(input, output)');
        $this->parser->advance();
        $this->parser->compileTerm();
        $term = $this->parser->toSimpleXML();
        $this->assertEquals('JackCompiler', $term->identifier[0]);
        $this->assertEquals('.', $term->symbol[0]);
        $this->assertEquals('compileClass', $term->identifier[1]);
        $this->assertEquals('(', $term->symbol[1]);
        $this->assertEquals('input', $term->expressionList->expression[0]->term->identifier);
        $this->assertEquals('output', $term->expressionList->expression[1]->term->identifier);
        $this->assertEquals(')', $term->symbol[2]);
    }
    
    public function testParens()
    {
        $this->writeTestProgram('(JackCompiler > JackTokenizer)');
        $this->parser->advance();
        $this->parser->compileTerm();
        $term = $this->parser->toSimpleXML();
        $this->assertEquals('(', $term->symbol[0]);
        $this->assertEquals('JackCompiler', $term->expression->term[0]->identifier);
        $this->assertEquals('>', $term->expression->symbol);
        $this->assertEquals('JackTokenizer', $term->expression->term[1]->identifier);
        $this->assertEquals(')', $term->symbol[1]);
    }
    
    public function testNestedParens()
    {
        $this->writeTestProgram('((a + b) * c)');
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
        $this->assertEquals('a', $thirdExpr->term[0]->identifier);
        $this->assertEquals('+', $thirdExpr->symbol);
        $this->assertEquals('b', $thirdExpr->term[1]->identifier);
        $this->assertEquals(')', $secondExpr->term[0]->symbol[1]);
        $this->assertEquals('*', $secondExpr->symbol);
        $this->assertEquals('c', $secondExpr->term[1]->identifier);
        $this->assertEquals(')', $root->term->symbol[1]);
    }
}

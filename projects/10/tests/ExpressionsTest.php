<?php

namespace JackTests;

class ExpressionsTest extends CompilerTestCase
{
    public function testMinIntegerConstant()
    {
        $this->writeTestProgram('0');
        $this->parser->advance();
        $this->parser->compileTerm();
        
        $term = simplexml_import_dom($this->parser->getCtx());
        $this->assertEquals('term', $term->getName());
        $this->assertEquals('0', $term->integerConstant);
    }
    
    public function testMaxIntegerConstant()
    {
        $this->writeTestProgram('32767');
        $this->parser->advance();
        $this->parser->compileTerm();
        
        $term = simplexml_import_dom($this->parser->getCtx());
        $this->assertEquals('term', $term->getName());
        $this->assertEquals('32767', $term->integerConstant);
    }
    
    public function testStringConstant()
    {
        $this->writeTestProgram('"The quick brown fox jumps over the lazy dog"');
        $this->parser->advance();
        $this->parser->compileTerm();
        
        $term = simplexml_import_dom($this->parser->getCtx());
        $this->assertEquals('term', $term->getName());
        $this->assertEquals('The quick brown fox jumps over the lazy dog', $term->stringConstant);
    }
    
    public function testKeywordConstant()
    {
        $this->writeTestProgram('true | false & null + this');
        $this->parser->advance();
        $this->parser->compileExpression();
        $expr = simplexml_import_dom($this->parser->getCtx(), 'SimpleXMLIterator');
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
}

<?php

namespace JackTests;

use JackCompiler\JackTokenizer;
use JackCompiler\TokenizerError;

class JackTokenizerTest extends \PHPUnit_Framework_TestCase
{
    protected $fp;

    protected function setUp()
    {
        $this->fp = fopen('php://memory', 'w');
        rewind($this->fp);
    }

    protected function tearDown()
    {
        fclose($this->fp);
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage arquivo não encontrado: this file does not exists
     */
    public function testFileNotFound()
    {
        new JackTokenizer('this file does not exists');
    }

    public function testEmptyFile()
    {
        $jt = new JackTokenizer($this->fp);
        $this->assertFalse($jt->hasMoreTokens());
    }

    public function testSingleToken()
    {
        fwrite($this->fp, " \n\r class \t ");
        rewind($this->fp);
        $jt = new JackTokenizer($this->fp);
        $this->assertTrue($jt->hasMoreTokens());
        $jt->advance();
        $this->assertFalse($jt->hasMoreTokens());
    }

    public function testMultipleTokens()
    {
        fwrite($this->fp, "class return");
        rewind($this->fp);
        $jt = new JackTokenizer($this->fp);
        $this->assertTrue($jt->hasMoreTokens());
        $jt->advance();
        $this->assertTrue($jt->hasMoreTokens());
        $jt->advance();
        $this->assertFalse($jt->hasMoreTokens());
    }

    public function testTokenType()
    {
        fwrite($this->fp, "class { size 510 \"The quick brown fox jumps over the lazy dog\"");
        rewind($this->fp);
        $jt = new JackTokenizer($this->fp);
        $this->assertTrue($jt->hasMoreTokens());
        $jt->advance();
        $this->assertEquals(JackTokenizer::KEYWORD, $jt->tokenType());

        $this->assertTrue($jt->hasMoreTokens());
        $jt->advance();
        $this->assertEquals(JackTokenizer::SYMBOL, $jt->tokenType());

        $this->assertTrue($jt->hasMoreTokens());
        $jt->advance();
        $this->assertEquals(JackTokenizer::IDENTIFIER, $jt->tokenType());

        $this->assertTrue($jt->hasMoreTokens());
        $jt->advance();
        $this->assertEquals(JackTokenizer::INT_CONST, $jt->tokenType());

        $this->assertTrue($jt->hasMoreTokens());
        $jt->advance();
        $this->assertEquals(JackTokenizer::STRING_CONST, $jt->tokenType());
    }

    public function testKeyword()
    {
        fwrite($this->fp, "class x return size > function");
        rewind($this->fp);
        $jt = new JackTokenizer($this->fp);
        $this->assertTrue($jt->hasMoreTokens());
        $jt->advance();
        $this->assertEquals('class', $jt->keyword());

        $this->assertTrue($jt->hasMoreTokens());
        $jt->advance();
        $this->assertTrue($jt->hasMoreTokens());
        $jt->advance();
        $this->assertEquals('return', $jt->keyword());

        $this->assertTrue($jt->hasMoreTokens());
        $jt->advance();
        $this->assertTrue($jt->hasMoreTokens());
        $jt->advance();
        $this->assertTrue($jt->hasMoreTokens());
        $jt->advance();
        $this->assertEquals('function', $jt->keyword());
    }
    
    /**
     * @expectedException JackCompiler\TokenizerError
     * @expectedExceptionMessage token atual não é uma keyword
     */
    public function testNotKeyword()
    {
        fwrite($this->fp, "klass");
        rewind($this->fp);
        $jt = new JackTokenizer($this->fp);
        $this->assertTrue($jt->hasMoreTokens());
        $jt->advance();
        $jt->keyword();
    }
    
    public function testIsKeyword()
    {
        fwrite($this->fp, "return lambda char");
        rewind($this->fp);
        $jt = new JackTokenizer($this->fp);
        
        $this->assertTrue($jt->hasMoreTokens());
        $jt->advance();
        $this->assertTrue($jt->isKeyword('return'));
        
        $this->assertTrue($jt->hasMoreTokens());
        $jt->advance();
        $this->assertFalse($jt->isKeyword('lambda'));
        
        $this->assertTrue($jt->hasMoreTokens());
        $jt->advance();
        $this->assertTrue($jt->isKeyword('int', 'char', 'php'));
    }

    public function testSymbol()
    {
        fwrite($this->fp, "(x)");
        rewind($this->fp);
        $jt = new JackTokenizer($this->fp);
        $this->assertTrue($jt->hasMoreTokens());
        $jt->advance();
        $this->assertEquals('(', $jt->symbol());

        $this->assertTrue($jt->hasMoreTokens());
        $jt->advance();
        $this->assertFalse($jt->isSymbol('x'));

        $this->assertTrue($jt->hasMoreTokens());
        $jt->advance();
        $this->assertEquals(')', $jt->symbol());
    }

    public function testIsSymbol()
    {
        fwrite($this->fp, ", @ &");
        rewind($this->fp);
        $jt = new JackTokenizer($this->fp);
        
        $this->assertTrue($jt->hasMoreTokens());
        $jt->advance();
        $this->assertTrue($jt->isSymbol(','));
        
        $this->assertTrue($jt->hasMoreTokens());
        $jt->advance();
        $this->assertFalse($jt->isSymbol('@'));
        
        $this->assertTrue($jt->hasMoreTokens());
        $jt->advance();
        $this->assertTrue($jt->isSymbol('|', '/', '&'));
    }
    
    public function testIdentifier()
    {
        fwrite($this->fp, "x Xy XyZ 10x");
        rewind($this->fp);
        $jt = new JackTokenizer($this->fp);
        $this->assertTrue($jt->hasMoreTokens());
        $jt->advance();
        $this->assertEquals('x', $jt->identifier());

        $this->assertTrue($jt->hasMoreTokens());
        $jt->advance();
        $this->assertEquals('Xy', $jt->identifier());

        $this->assertTrue($jt->hasMoreTokens());
        $jt->advance();
        $this->assertEquals('XyZ', $jt->identifier());

        $this->assertTrue($jt->hasMoreTokens());
        $jt->advance();
        $this->assertNull($jt->tokenType());
    }

    public function testIntVal()
    {
        fwrite($this->fp, "0 1 256 0777 x");
        rewind($this->fp);
        $jt = new JackTokenizer($this->fp);
        $this->assertTrue($jt->hasMoreTokens());
        $jt->advance();
        $this->assertSame(0, $jt->intVal());

        $this->assertTrue($jt->hasMoreTokens());
        $jt->advance();
        $this->assertSame(1, $jt->intVal());

        $this->assertTrue($jt->hasMoreTokens());
        $jt->advance();
        $this->assertSame(256, $jt->intVal());

        $this->assertTrue($jt->hasMoreTokens());
        $jt->advance();
        $this->assertSame(777, $jt->intVal());

        $this->assertTrue($jt->hasMoreTokens());
        $jt->advance();
        $this->assertNull($jt->intVal());
    }

    public function testStringVal()
    {
        fwrite($this->fp, "\"hello\" \"world\"");
        rewind($this->fp);
        $jt = new JackTokenizer($this->fp);
        $this->assertTrue($jt->hasMoreTokens());
        $jt->advance();
        $this->assertEquals(JackTokenizer::STRING_CONST, $jt->tokenType());
        $this->assertSame("hello", $jt->stringVal());

        $this->assertTrue($jt->hasMoreTokens());
        $jt->advance();
        $this->assertEquals(JackTokenizer::STRING_CONST, $jt->tokenType());
        $this->assertSame("world", $jt->stringVal());
    }

    public function testSingleLineComment()
    {
        fwrite($this->fp, "class // let class = function \n x / y");
        rewind($this->fp);
        $jt = new JackTokenizer($this->fp);
        $this->assertTrue($jt->hasMoreTokens());
        $jt->advance();
        $this->assertEquals(JackTokenizer::KEYWORD, $jt->tokenType());
        $this->assertSame("class", $jt->keyword());

        $this->assertTrue($jt->hasMoreTokens());
        $jt->advance();
        $this->assertEquals(JackTokenizer::IDENTIFIER, $jt->tokenType());
        $this->assertSame("x", $jt->identifier());

        $this->assertTrue($jt->hasMoreTokens());
        $jt->advance();
        $this->assertEquals(JackTokenizer::SYMBOL, $jt->tokenType());
        $this->assertSame("/", $jt->symbol());

        $this->assertTrue($jt->hasMoreTokens());
        $jt->advance();
        $this->assertEquals(JackTokenizer::IDENTIFIER, $jt->tokenType());
        $this->assertSame("y", $jt->identifier());
    }

    public function testMultiLineComment()
    {
        fwrite($this->fp, "return /** \n API Comment \n\r */ ab+cd");
        rewind($this->fp);
        $jt = new JackTokenizer($this->fp);
        $this->assertTrue($jt->hasMoreTokens());
        $jt->advance();
        $this->assertEquals(JackTokenizer::KEYWORD, $jt->tokenType());
        $this->assertSame("return", $jt->keyword());

        $this->assertTrue($jt->hasMoreTokens());
        $jt->advance();
        $this->assertEquals(JackTokenizer::IDENTIFIER, $jt->tokenType());
        $this->assertSame("ab", $jt->identifier());

        $this->assertTrue($jt->hasMoreTokens());
        $jt->advance();
        $this->assertEquals(JackTokenizer::SYMBOL, $jt->tokenType());
        $this->assertSame("+", $jt->symbol());

        $this->assertTrue($jt->hasMoreTokens());
        $jt->advance();
        $this->assertEquals(JackTokenizer::IDENTIFIER, $jt->tokenType());
        $this->assertSame("cd", $jt->identifier());
    }
}

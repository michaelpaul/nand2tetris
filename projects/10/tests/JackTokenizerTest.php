<?php

use JackCompiler\JackTokenizer;

class JackTokenizerTest extends PHPUnit_Framework_TestCase
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
        $this->assertNull($jt->symbol());

        $this->assertTrue($jt->hasMoreTokens());
        $jt->advance();
        $this->assertEquals(')', $jt->symbol());
    }

    public function testIdentifier()
    {
        fwrite($this->fp, "let size = 10 ;");
        rewind($this->fp);
        $jt = new JackTokenizer($this->fp);
        $this->assertTrue($jt->hasMoreTokens());
        $jt->advance();
        $this->assertNull($jt->identifier());

        $this->assertTrue($jt->hasMoreTokens());
        $jt->advance();
        $this->assertEquals('size', $jt->identifier());

        $this->assertTrue($jt->hasMoreTokens());
        $jt->advance();
        $this->assertNull($jt->identifier());

        $this->assertTrue($jt->hasMoreTokens());
        $jt->advance();
        $this->assertNull($jt->identifier());

        $this->assertTrue($jt->hasMoreTokens());
        $jt->advance();
        $this->assertNull($jt->identifier());
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
        // echo stream_get_contents($this->fp);
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
}

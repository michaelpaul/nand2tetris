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

}

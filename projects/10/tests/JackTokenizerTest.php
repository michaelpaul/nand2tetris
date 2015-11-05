<?php

use JackCompiler\JackTokenizer;

class JackTokenizerTest extends PHPUnit_Framework_TestCase
{
    /**
     * @expectedException Exception
     * @expectedExceptionMessage arquivo não encontrado: this file does not exists
     */
    public function testFileNotFound()
    {
        new JackTokenizer('this file does not exists');
    }

    public function testHasMoreTokens()
    {
        $fp = fopen('php://memory', 'r');
        $jt = new JackTokenizer($fp);
        $this->assertFalse($jt->hasMoreTokens());
        fclose($fp);

        $fp = fopen('php://memory', 'w');
        fwrite($fp, " \n\r class \t ");
        $jt = new JackTokenizer($fp);
        $this->assertTrue($jt->hasMoreTokens());
        $jt->advance();
        // $jt now has the current token...
        $this->assertFalse($jt->hasMoreTokens());
    }
}

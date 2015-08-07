<?php

use VMTranslator\Parser;

/**
 * Class ParserTest
 */
class ParserTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->p = new Parser(dirname(__FILE__) . '/sample.vm');
    }

    public function testAdvance()
    {
        $this->assertTrue($this->p->hasMoreCommands());
        $this->p->advance();
        $this->assertEquals(Parser::C_PUSH, $this->p->commandType());
        $this->assertEquals('constant', $this->p->arg1());
        $this->assertEquals(7, $this->p->arg2());

        $this->assertTrue($this->p->hasMoreCommands());
        $this->p->advance();
        $this->assertEquals(Parser::C_PUSH, $this->p->commandType());
        $this->assertEquals('constant', $this->p->arg1());
        $this->assertEquals(8, $this->p->arg2());

        $this->assertTrue($this->p->hasMoreCommands());
        $this->p->advance();
        $this->assertEquals(Parser::C_ARITHMETIC, $this->p->commandType());
        $this->assertEquals('add', $this->p->arg1());
        $this->assertNull($this->p->arg2());
    }
}

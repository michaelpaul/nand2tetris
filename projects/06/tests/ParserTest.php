<?php

// namespace AssemblerTests;

use Assembler\Parser;

/**
 * Class ParserTest
 */
class ParserTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->p = new Parser(dirname(__FILE__) . '/sample.asm');
        $this->assertTrue($this->p->hasMoreCommands(), 'Abriu arquivo com comandos');
    }

    public function testAdvance()
    {
        // a command
        $this->p->advance();
        $this->assertEquals(Parser::A_COMMAND, $this->p->commandType());
        $this->assertEquals('R0', $this->p->symbol());

        // dest=comp
        $this->p->advance();
        $this->assertEquals(Parser::C_COMMAND, $this->p->commandType());
        $this->assertEquals('D', $this->p->dest());
        $this->assertEquals('M-1', $this->p->comp());

        // symbol
        $this->p->advance();
        $this->assertEquals(Parser::A_COMMAND, $this->p->commandType());
        $this->assertEquals('LOOP', $this->p->symbol());

        // comp;jump
        $this->p->advance();
        $this->assertEquals(Parser::C_COMMAND, $this->p->commandType());
        $this->assertNull($this->p->dest());
        $this->assertEquals('0', $this->p->comp());
        $this->assertEquals('JMP', $this->p->jump());

        // label
        $this->p->advance();
        $this->assertEquals(Parser::L_COMMAND, $this->p->commandType());
        $this->assertEquals('HERE', $this->p->symbol());

        // dest=comp;jump
        $this->p->advance();
        $this->assertEquals(Parser::C_COMMAND, $this->p->commandType());
        $this->assertEquals('AMD', $this->p->dest());
        $this->assertEquals('D+M', $this->p->comp());
        $this->assertEquals('JMP', $this->p->jump());
    }
}

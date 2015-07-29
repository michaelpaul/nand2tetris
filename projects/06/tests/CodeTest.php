<?php

use Assembler\Code;

class CodeTest extends PHPUnit_Framework_TestCase
{
    public function testDest()
    {
        $c = new Code();
        $this->assertEquals('000', $c->dest(''));
        $this->assertEquals('001', $c->dest('M'));
        $this->assertEquals('010', $c->dest('D'));
        $this->assertEquals('011', $c->dest('MD'));
        $this->assertEquals('100', $c->dest('A'));
        $this->assertEquals('101', $c->dest('AM'));
        $this->assertEquals('110', $c->dest('AD'));
        $this->assertEquals('111', $c->dest('AMD'));
    }

    public function testComp()
    {
        $c = new Code();
        // a=0
        $this->assertEquals('0101010', $c->comp('0'));
        $this->assertEquals('0111111', $c->comp('1'));
        $this->assertEquals('0111010', $c->comp('-1'));
        $this->assertEquals('0001100', $c->comp('D'));
        $this->assertEquals('0110000', $c->comp('A'));
        $this->assertEquals('0001101', $c->comp('!D'));
        $this->assertEquals('0110001', $c->comp('!A'));
        $this->assertEquals('0001111', $c->comp('-D'));
        $this->assertEquals('0110011', $c->comp('-A'));
        $this->assertEquals('0011111', $c->comp('D+1'));
        $this->assertEquals('0110111', $c->comp('A+1'));
        $this->assertEquals('0001110', $c->comp('D-1'));
        $this->assertEquals('0110010', $c->comp('A-1'));
        $this->assertEquals('0000010', $c->comp('D+A'));
        $this->assertEquals('0010011', $c->comp('D-A'));
        $this->assertEquals('0000111', $c->comp('A-D'));
        $this->assertEquals('0000000', $c->comp('D&A'));
        $this->assertEquals('0010101', $c->comp('D|A'));
        // a=1
        $this->assertEquals('1110000', $c->comp('M'));
        $this->assertEquals('1110001', $c->comp('!M'));
        $this->assertEquals('1110011', $c->comp('-M'));
        $this->assertEquals('1110111', $c->comp('M+1'));
        $this->assertEquals('1110010', $c->comp('M-1'));
        $this->assertEquals('1000010', $c->comp('D+M'));
        $this->assertEquals('1010011', $c->comp('D-M'));
        $this->assertEquals('1000111', $c->comp('M-D'));
        $this->assertEquals('1000000', $c->comp('D&M'));
        $this->assertEquals('1010101', $c->comp('D|M'));
    }

    public function testJump()
    {
        $c = new Code;
        $this->assertEquals('001', $c->jump('JGT'));
        $this->assertEquals('010', $c->jump('JEQ'));
        $this->assertEquals('011', $c->jump('JGE'));
        $this->assertEquals('100', $c->jump('JLT'));
        $this->assertEquals('101', $c->jump('JNE'));
        $this->assertEquals('110', $c->jump('JLE'));
        $this->assertEquals('111', $c->jump('JMP'));
    }
}

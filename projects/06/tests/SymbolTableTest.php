<?php

use Assembler\SymbolTable;

class SymbolTableTest extends PHPUnit_Framework_TestCase
{
    public function testSymbolTable()
    {
        $st = new SymbolTable;
        $st->addEntry('x', 128);
        $this->assertTrue($st->contains('x'));
        $this->assertFalse($st->contains('z'));
        $st->addEntry('z', 129);
        $this->assertTrue($st->contains('z'));
        $this->assertEquals(128, $st->getAddress('x'));
        $this->assertEquals(129, $st->getAddress('z'));
        $this->assertNull($st->getAddress('nil'));
    }
}

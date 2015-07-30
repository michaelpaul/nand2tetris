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

    public function predefinedSymbolsProvider()
    {
        $symbols = array(
            array('SP', 0),
            array('LCL', 1),
            array('ARG', 2),
            array('THIS', 3),
            array('THAT', 4),
            array('R0', 0),
            array('R5', 5),
            array('R10', 10),
            array('R15', 15),
            array('SCREEN', 0x4000),
            array('KBD', 0x6000),
        );
        return $symbols;
    }

    /**
     * @dataProvider predefinedSymbolsProvider
     */
    public function testPredefinedSymbols($symbol, $address)
    {
        $st = new SymbolTable;

        $this->assertTrue($st->contains($symbol));
        $this->assertEquals($address, $st->getAddress($symbol));
    }
}

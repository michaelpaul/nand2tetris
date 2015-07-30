<?php

use Assembler\Main;
use Assembler\SymbolTable;

class MainTest extends PHPUnit_Framework_TestCase
{
    public function testResolveSymbols()
    {
        $sample = __DIR__ . '/sample.asm';
        $m = new Main;

        // predefined
        $this->assertEquals(0, $m->getACommandValue('R0'));
        $this->assertEquals(15, $m->getACommandValue('R15'));

        // labels
        $m->firstPass($sample);
        $this->assertEquals(4, $m->getACommandValue('HERE'), 'Goto here');

        // variables
        $this->assertEquals(16, $m->getACommandValue('sum'), 'Base address = 16');
        $this->assertEquals(17, $m->getACommandValue('i'), 'Nova variável');
        $this->assertEquals(17, $m->getACommandValue('i'), 'Mesmo endereço');
        $this->assertEquals(18, $m->getACommandValue('CaseInSensiTive'));
        $this->assertEquals(128, $m->getACommandValue('128'), 'Decimal simples');
        $this->assertEquals(19, $m->getACommandValue('-128'), 'Decimal negativo vira variável?');
        $this->assertEquals(20, $m->getACommandValue('$a_z.A_Z:0_9'), 'Caracteres permitidos');
    }
}

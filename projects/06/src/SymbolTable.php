<?php

namespace Assembler;

class SymbolTable
{
    private $table;

    function __construct()
    {
        $this->table = array();
        $this->addEntry('SP', 0);
        $this->addEntry('LCL', 1);
        $this->addEntry('ARG', 2);
        $this->addEntry('THIS', 3);
        $this->addEntry('THAT', 4);

        for ($i = 0; $i < 16; $i++) {
            $this->addEntry('R' . $i, $i);
        }

        $this->addEntry('SCREEN', 0x4000);
        $this->addEntry('KBD', 0x6000);
    }

    public function addEntry($symbol, $address)
    {
        $this->table[$symbol] = $address;
    }

    public function contains($symbol)
    {
        return array_key_exists($symbol, $this->table);
    }

    public function getAddress($symbol)
    {
        if ($this->contains($symbol)) {
            return $this->table[$symbol];
        }
    }
}

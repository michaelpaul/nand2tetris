<?php

namespace Assembler;

class SymbolTable
{
    private $table;

    function __construct()
    {
        $this->table = array();
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

<?php

namespace JackCompiler;

class SymbolTable
{
    protected $hashTable;
    protected $varIndex;

    /**
     * Creates a new copy of the symbol table
     */
    public function __construct()
    {
        $this->hashTable = array();
        $this->varIndex = 0;
    }

    public function addVarDec(\SimpleXMLElement $varDecNode)
    {
        $identifiers = $varDecNode->xpath('identifier');

        if ($varDecNode->keyword->count() > 1) {
            $type = $varDecNode->keyword[1];
        } else {
            $type = (string) $identifiers[0];
            array_shift($identifiers);
        }

        foreach ($identifiers as $node) {
            $name = (string) $node;
            $this->hashTable[$name] = new Symbol($name, (string) $type, 'var', $this->varIndex);
            $this->varIndex++;
        }
    }

    public function get($identifier)
    {
        return $this->hashTable[$identifier];
    }

    public function contains($identifier)
    {
        return array_key_exists($identifier, $this->hashTable);
    }

    // ** Book API ** //

    /**
     * Starts a new subroutine scope (i.e., resets the subroutine's symbol table)
     *
     * @return void
     */
    public function startSubroutine()
    {
        # code...
    }

    /**
     * Defines a new identifier of a given name, type and kind and assigns it
     * a running index. STATIC and FIELD identifiers have a class scope,
     * while ARG and VAR identifiers have a subroutine scope.
     *
     * @param String $name
     * @param String $type
     * @param enum $kind (STATIC, FIELD, ARG, VAR)
     * @return void
     */
    public function define($name, $type, $kind)
    {
     # code...
    }

    /**
     * Returns the number of variables of the given kind already defined in the
     * current scope.
     *
     * @param enum $kind (STATIC, FIELD, ARG, VAR)
     * @return int
     */
    public function varCount($kind)
    {
        # code...
    }

    /**
     * Returns the kind of the named identifier in the current scope. If the
     * identifier is unknow in the current scope, returns NONE.
     *
     * @param String $name
     * @return enum (STATIC, FIELD, ARG, VAR)
     */
    public function kindOf($name)
    {
        # code...
    }

    /**
     * Returns the type of the named identifier in the current scope.
     *
     * @param String $name
     * @return String
     */
    public function typeOf($name)
    {
        # code...
    }

    /**
     * Returns the index assigned to the named identifier.
     * @param String $name
     * @return int
     */
    public function indexOf($name)
    {
        # code...
    }
}

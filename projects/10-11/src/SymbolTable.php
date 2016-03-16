<?php 

namespace JackCompiler;

class SymbolTable
{
    protected $hashTable;
    protected $varIndex;
    
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
}

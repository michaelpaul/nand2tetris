<?php 

namespace JackCompiler;

class Symbol
{
    public $name;
    public $type;
    public $kind;
    public $index;
    
    public function __construct($name, $type, $kind, $index)
    {
        $this->name = $name;
        $this->type = $type;
        $this->kind = $kind;
        $this->index = $index;
    }
}

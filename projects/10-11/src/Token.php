<?php

namespace JackCompiler;

class Token
{
    public $type;
    public $val;
    
    public function __construct($type, $val)
    {
        $this->type = $type;
        $this->val = $val;
    }
}

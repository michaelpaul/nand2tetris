<?php 

namespace JackCompiler;

/*
// unit test
// $compiler = new Main('tests/programs/11/ConvertToBin/Main.jack', 'php://memory');
// $compiler->compile();
// ...
*/

class Main 
{
    protected $input;
    protected $output;
    
    /**
     * @param $input arquivo jack ou diretório contendo arquivos jack
     * @param $output arquivo.vm caso input seja apenas um arquivo
     */
    function __construct($input, $output = null)
    {
        $this->input = $input;
        if ($output) {
            $this->output = fopen($output, 'w');
        }
    }
    
    public function compile()
    {
        $engine = new CompilationEngine($this->input);
        $engine->compileClass();
    }
    
    public function __destruct()
    {
        if (is_resource($this->output)) {
            fclose($this->output);
        }
    }
}

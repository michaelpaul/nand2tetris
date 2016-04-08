<?php 

namespace JackCompiler;

/*
// unit test?
// $compiler = new Main('tests/programs/11/ConvertToBin/Main.jack', 'php://memory');
// $compiler->compile();
// ...
*/

class Main
{
    protected $input;
    
    /**
     * @param $input arquivo jack ou diretório contendo arquivos jack
     */
    public function __construct($input)
    {
        $this->input = array();
        
        if (is_null($input)) {
            $input = getcwd();
        }
        
        if (is_file($input) && substr($input, -5, 5) == '.jack') {
            $this->input[] = $input;
        } elseif (is_dir($input)) {
            $this->input = glob(realpath($input) . DIRECTORY_SEPARATOR . "*.jack");
        } else {
            throw new \Exception("$input não é um arquivo ou diretório válido");
        }
    }
    
    public function compile()
    {
        foreach ($this->input as $jackFile) {
            $engine = new CompilationEngine($jackFile);
            $engine->compileClass();
        }
    }
}

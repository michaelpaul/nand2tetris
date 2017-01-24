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
    
    public function compile($dumpAst = false)
    {
        foreach ($this->input as $jackFile) {
            $engine = new CompilationEngine($jackFile);
            $engine->setSymbolTable(new SymbolTable);
            
            // setup writer
            $vmFilename = str_replace('.jack', '.vm', $jackFile);
            $engine->setWriter(new VMWriter($vmFilename));
            
            try {
                $engine->compileClass();
            } catch(\Exception $e) {
                echo "Erro: " . $e->getMessage() . " em " . $jackFile . PHP_EOL;
            }

            if ($dumpAst) {
                $xmlFilename = str_replace('.jack', '.ast', $jackFile);
                $engine->toXML($xmlFilename);
            }
        }
    }
}

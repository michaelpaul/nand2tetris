<?php 

namespace JackCompiler;

class VMWriter
{
    private $fp;
    private $functionName;
    
    /**
     * Creates a new file and prepares it for writing
     */
    public function __construct($output)
    {
        $this->fp = fopen($output, 'w');
    }
    
    /**
     * @param $kind valor retornado pela SymbolTable
     */
    public function getSegmentName($kind)
    {
        switch ($kind) {
            case 'var':
                $kind = 'local';
                break;
            case 'arg':
                $kind = 'argument';
                break;
            case 'field':
                $kind = 'this';
                break;
        }
        return $kind;
    }
    
    /**
     * Writes a VM push command
     */
    public function writePush($segment, $index)
    {
        $segment = $this->getSegmentName($segment);
        $this->writeCode("push $segment $index");
    }
    
    /**
     * Writes a VM pop command
     */
    public function writePop($segment, $index)
    {
        $segment = $this->getSegmentName($segment);
        $this->writeCode("pop $segment $index");
    }
    
    /**
     * Writes a VM arithmetic command
     */
    public function writeArithmetic($command)
    {
        switch ($command) {
            case '+':
                $op = 'add';
                break;
            case '-':
                $op = 'sub';
                break;
            case '*':
                $op = 'call Math.multiply 2';
                break;
            case '/':
                $op = 'call Math.divide 2';
                break;
            case '&':
                $op = 'and';
                break;
            case '|':
                $op = 'or';
                break;
            case '<':
                $op = 'lt';
                break;
            case '>':
                $op = 'gt';
                break;
            case '=':
                $op = 'eq';
                break;
            default:
                throw new \Exception("Operador não implementado \"$command\"");
        }
        $this->writeCode($op);
    }
    
    public function writeUnaryOp($command)
    {
        if ($command == '-') {
            $op = 'neg';
        } elseif ($command == '~') {
            $op = 'not';
        }
        $this->writeCode($op);
    }
    
    /**
     * Writes a VM label command
     * @param $label string
     */
    public function writeLabel($label)
    {
        $this->writeCode("label $label", false);
        return $label;
    }
    
    /**
     * Writes a VM goto command
     * @param $label string
     */
    public function writeGoto($label)
    {
        $this->writeCode("goto $label");
    }
    
    /**
     * Writes a VM if-goto command
     * @param $label string
     */
    public function writeIf($label)
    {
        $this->writeCode("if-goto $label");
    }
    
    /**
     * Writes a VM call command
     * @param $name string
     * @param $nArgs int
     */
    public function writeCall($name, $nArgs)
    {
        $this->writeCode("call $name $nArgs");
    }
    
    /**
     * Writes a VM function command
     * @param $name string
     * @param $nLocals int
     */
    public function writeFunction($name, $nLocals)
    {
        $this->functionName = $name;
        $this->writeCode("function $name $nLocals", false);
    }
    
    /**
     * Writes a VM return command
     */
    public function writeReturn()
    {
        $this->writeCode('return');
    }
    
    /**
     * Closes the output file.
     */
    public function close()
    {
        if (is_resource($this->fp)) {
            fclose($this->fp);
        }
    }
    
    protected function writeCode($code, $indent = true)
    {
        $code = "$code\n";
        if ($indent) {
            $code = "\t" . $code;
        }
        fwrite($this->fp, $code);
    }
    
    protected function writeBlock(array $code)
    {
        foreach ($code as $line) {
            $this->writeCode($line);
        }
    }
}

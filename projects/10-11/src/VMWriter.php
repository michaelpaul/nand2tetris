<?php 

namespace JackCompiler;

class VMWriter
{
    private $fp;
    
    /**
     * Creates a new file and prepares it for writing
     */
    public function __construct($output)
    {
        $this->fp = fopen($output, 'w');
    }
    
    /**
     * Writes a VM push command
     */
    public function writePush($segment, $index)
    {
        switch ($segment) {
            case 'var':
                $segment = 'local';
                break;
            case 'arg':
                $segment = 'argument';
                break;
        }
        $this->writeCode(array("push $segment $index"));
    }
    
    /**
     * Writes a VM pop command
     */
    public function writePop($segment, $index)
    {
        switch ($segment) {
            case 'var':
                $segment = 'local';
                break;
            case 'arg':
                $segment = 'argument';
                break;
        }
        $this->writeCode(array("pop $segment $index"));
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
        $this->writeCode(array($op));
    }
    
    public function writeUnaryOp($command)
    {
        if ($command == '-') {
            $op = 'neg';
        } elseif ($command == '~') {
            $op = 'not';
        }
        $this->writeCode(array($op));
    }
    
    /**
     * Writes a VM label command
     * @param $label string
     */
    public function writeLabel($label)
    {
        # code...
    }
    
    /**
     * Writes a VM goto command
     * @param $label string
     */
    public function writeGoto($label)
    {
        # code...
    }
    
    /**
     * Writes a VM if-goto command
     * @param $label string
     */
    public function writeIf($label)
    {
        # code...
    }
    
    /**
     * Writes a VM call command
     * @param $name string
     * @param $nArgs int
     */
    public function writeCall($name, $nArgs)
    {
        $this->writeCode(array("call $name $nArgs"));
    }
    
    /**
     * Writes a VM function command
     * @param $name string
     * @param $nLocals int
     */
    public function writeFunction($name, $nLocals)
    {
        $this->writeCode(array("function $name $nLocals"));
    }
    
    /**
     * Writes a VM return command
     */
    public function writeReturn()
    {
        $this->writeCode(array('return'));
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
    
    protected function writeCode(array $code)
    {
        if (count($code) > 0) {
            fwrite($this->fp, implode("\n", $code) . "\n");
        }
    }
}

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
        // @DEV
        $output = 'php://stdout';
        $this->fp = fopen($output, 'w');
        $this->writeCode(array('// output to: ' . $output));
    }
    
    /**
     * Writes a VM push command
     */
    public function writePush($segment, $index)
    {
        # code...
    }
    
    /**
     * Writes a VM pop command
     */
    public function writePop($segment, $index)
    {
        # code...
    }
    
    /**
     * Writes a VM arithmetic command
     */
    public function writeArithmetic($command)
    {
        # code...
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

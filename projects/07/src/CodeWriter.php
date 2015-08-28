<?php

namespace VMTranslator;

class CodeWriter
{
    private $fp;
    private $filename;

    /**
     * Opens the output file/stream and gets ready to write into it.
     */
    function __construct($outputFile)
    {
        $this->fp = fopen($outputFile, 'w');
    }

    /**
     * Informs the code writer that the translation of a new VM file is started.
     */
    public function setFilename($filename)
    {
        $this->filename = $filename;
        $this->writeCode(array('// @filename: ' . $filename));
    }

    /**
     * Writes the assembly code that is the translation of the given arithmetic command.
     */
    public function writeArithmetic($command)
    {
        switch ($command) {
            case 'add':
                $this->pop('R13');
                $this->pop('R14');
                $code = array(
                    // D = R13+R14
                    '@R13',
                    'D=M',
                    '@R14',
                    'D=D+M',
                );
                $this->writeCode($code);
                $this->pushD();
                break;
            case 'eq':
                $this->pop('R13');
                $this->pop('R14');
                $this->writeCode(array(
                    // @TODO compute result in D
                    '@here',
                    'D;JEQ',
                    '(here)',
                    'compute',
                ));
                $this->pushD();
                break;
        }
    }

    /**
     * Writes the assembly code that is the translation of the given command,
     * where command is either C_PUSH or C_POP.
     */
    public function writePushPop($command, $segment, $index)
    {
        if ($command == Parser::C_PUSH && $segment == 'constant' ) {
            $this->writeCode(array(
                '// @ push constant ' . $index,
                '@' . $index,
                'D=A',
            ));
            $this->pushD();
        }
    }

    protected function writeCode(array $code)
    {
        if (count($code) > 0) {
            fwrite($this->fp, implode("\n", $code) . "\n");
        }
    }

    /**
     * pop stack into $dest variable
     */
    protected function pop($dest)
    {
        $this->writeCode(array(
            '@SP',
            'A=M-1',
            'D=M',
            '@' . $dest,
            'M=D',
            '@SP',
            'M=M-1',
        ));
    }

    protected function spInc()
    {
        $this->writeCode(array(
            // sp++
            '@SP',
            'M=M+1',
        ));
    }

    /**
     * Push D register to the top of the stack
     */
    protected function pushD()
    {
        $this->writeCode(array(
            '@SP',
            'A=M',
            'M=D',
        ));
        $this->spInc();
    }

    /**
     * Closes the output file.
     */
    public function close()
    {
        if ($this->fp) {
            // fclose($this->fp);
        }
    }

    public function __destruct()
    {
        $this->close();
    }
}

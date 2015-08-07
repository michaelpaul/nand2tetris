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
        if ($command == 'add') {
            $code = array(
                // pop R5
                '// @ add',
                '@SP',
                'A=M-1',
                'D=M',
                '@R5',
                'M=D',
                '@SP',
                'M=M-1',
                // pop R6
                '@SP',
                'A=M-1',
                'D=M',
                '@R6',
                'M=D',
                '@SP',
                'M=M-1',
                // D = R5+R6
                '@R5',
                'D=M',
                '@R6',
                'D=D+M',
                // push D
                '@SP',
                'A=M',
                'M=D',
                // sp++
                '@SP',
                'M=M+1',
            );
            $this->writeCode($code);
        }
    }

    /**
     * Writes the assembly code that is the translation of the given command,
     * where command is either C_PUSH or C_POP.
     */
    public function writePushPop($command, $segment, $index)
    {
        if ($command == Parser::C_PUSH && $segment == 'constant' ) {
            $code = array(
                '// @ push constant ' . $index,
                '@' . $index,
                'D=A',
                '@SP',
                'A=M',
                'M=D',
                // sp++
                '@SP',
                'M=M+1',
            );
            $this->writeCode($code);
        }
    }

    protected function writeCode(array $code)
    {
        if (count($code) > 0) {
            fwrite($this->fp, implode("\n", $code) . "\n");
        }
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

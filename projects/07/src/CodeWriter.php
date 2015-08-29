<?php

namespace VMTranslator;

class CodeWriter
{
    private $fp;
    private $filename;
    private $label_counter = 0;

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
                    '@R13',
                    'D=M',
                    '@R14',
                    'D=D+M',
                );
                $this->writeCode($code);
                $this->pushD();
                break;
            case 'sub':
                $this->pop('R13');
                $this->pop('R14');
                $code = array(
                    '@R13',
                    'D=M',
                    '@R14',
                    'D=M-D',
                );
                $this->writeCode($code);
                $this->pushD();
                break;
            case 'neg':
                $this->pop('R13');
                $code = array(
                    '@R13',
                    'D=-M',
                );
                $this->writeCode($code);
                $this->pushD();
                break;
            case 'eq':
                $this->pop('R13');
                $this->pop('R14');
                $this->eq('R13', 'R14');
                $this->pushD();
                break;
            case 'gt':
                $this->pop('R13');
                $this->pop('R14');
                $this->gt('R13', 'R14');
                $this->pushD();
                break;
            case 'lt':
                $this->pop('R13');
                $this->pop('R14');
                $this->lt('R13', 'R14');
                $this->pushD();
                break;
            case 'not':
                $this->pop('R13');
                $code = array(
                    '@R13',
                    'D=!M',
                );
                $this->writeCode($code);
                $this->pushD();
                break;
            case 'and':
                $this->pop('R13');
                $this->pop('R14');
                $code = array(
                    '@R13',
                    'D=M',
                    '@R14',
                    'D=D&M',
                );
                $this->writeCode($code);
                $this->pushD();
                break;
            case 'or':
                $this->pop('R13');
                $this->pop('R14');
                $code = array(
                    '@R13',
                    'D=M',
                    '@R14',
                    'D=D|M',
                );
                $this->writeCode($code);
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
     * Teste lógico baseado na diferença entre $x, $y e 0
     *
     * @param string $op operador lógico
     * @param string $x variavel/constante
     * @param string $y variavel/constante
     * @return array hack assembly
     */
    protected function test($op, $x, $y)
    {
        $true = '-1';
        $false = '0';
        $id = '_VMTEST' . $op . $this->label_counter;
        $this->label_counter++;
        $this->writeCode(array(
            '// test ' . $op,
            '@' . $x,
            'D=M',
            '@' . $y,
            'D=D-M', // x - y
            "@FALSE_$id",
            'D;' . $op, // if ($x - $y $op 0) goto f;
            '@r',
            'M=' . $true, // $r = true;
            "@END_$id",
            '0;JMP', // goto end;
            "(FALSE_$id)", // f:
            '@r',
            'M=' . $false, // $r = false;
            "(END_$id)", // end:
            '@r',
            'D=M', // return $r;
        ));
    }

    protected function eq($x, $y)
    {
        return $this->test('JNE', $x, $y);
    }

    protected function gt($x, $y)
    {
        return $this->test('JGE', $x, $y);
    }

    protected function lt($x, $y)
    {
        return $this->test('JLE', $x, $y);
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

<?php

namespace VMTranslator;

class CodeWriterException extends \Exception
{
}

class CodeWriter
{
    private $fp;
    private $filename;
    private $label_counter = 0;

    /**
     * Opens the output file/stream and gets ready to write into it.
     */
    public function __construct($outputFile)
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
     * Executes pop/push operations using the virtual memory segments
     */
    public function writePushPop($command, $segment, $index)
    {
        $base = array(
            'local' => 'LCL',
            'argument' => 'ARG',
            'this' => 'THIS',
            'that' => 'THAT',
            'temp' => '5',
        );
        $static_id = str_replace(DIRECTORY_SEPARATOR, '_', $this->filename) .
            '.' . $index;

        if ($command == Parser::C_PUSH) {
            switch ($segment) {
                case 'local':
                case 'argument':
                case 'this':
                case 'that':
                case 'temp':
                    $comp = $segment == 'temp' ? 'D+A' : 'D+M';
                    $this->writeCode(array(
                        // push segment
                        '@' . $index,
                        'D=A', // D = index
                        '@' . $base[$segment],
                        'A=' . $comp, // A = index + segment base
                        'D=M',   // D = M[A]
                    ));
                    $this->pushD();
                    break;
                case 'constant':
                    $this->writeCode(array(
                        '// @ push constant ' . $index,
                        '@' . $index,
                        'D=A',
                    ));
                    $this->pushD();
                    break;
                case 'pointer':
                    if ($index < 0 || $index > 1) {
                        throw new CodeWriterException('Push em um ponteiro inválido');
                    }
                    $ptr = array('THIS', 'THAT');
                    $this->writeCode(array(
                        '@' . $ptr[$index],
                        'D=M'
                    ));
                    $this->pushD();
                    break;
                case 'static':
                    $this->writeCode(array(
                        '@' . $static_id,
                        'D=M'
                    ));
                    $this->pushD();
                    break;
                default:
                    throw new CodeWriterException('Push em um segmento desconhecido: ' . $segment);
            }
        } elseif ($command == Parser::C_POP) {
            switch ($segment) {
                case 'local':
                case 'argument':
                case 'this':
                case 'that':
                case 'temp':
                    $this->pop('R13');
                    $comp = $segment == 'temp' ? 'D+A' : 'D+M';
                    $this->writeCode(array(
                        // R13: top of stack value, R14: segment address
                        '@' . $index,
                        'D=A', // D = index
                        '@' . $base[$segment],
                        'D=' . $comp, // A = index + segment base
                        '@R14',
                        'M=D', // R14 = segment address
                        '@R13',
                        'D=M', // D = R13
                        '@R14',
                        'A=M',
                        'M=D' // M[R14] = D
                    ));
                    break;
                case 'pointer':
                    if ($index < 0 || $index > 1) {
                        throw new CodeWriterException('Pop em um ponteiro inválido');
                    }
                    $ptr = array('THIS', 'THAT');
                    $this->pop('R13');
                    $this->writeCode(array(
                        '@R13',
                        'D=M',
                        '@' . $ptr[$index],
                        'M=D'
                    ));
                    break;
                case 'static':
                    $this->pop('R13');
                    $this->writeCode(array(
                        '@R13',
                        'D=M',
                        '@' . $static_id,
                        'M=D'
                    ));
                    break;
                default:
                    throw new CodeWriterException('Pop em um segmento desconhecido: ' . $segment);
            }
        }
    }

    /**
     * Traduzir label
     */
    public function writeLabel($label)
    {
        $label = strtoupper($label);
        $this->writeCode(array(
            "($label)"
        ));
    }

    /**
     * Traduzir goto $label
     */
    public function writeGoto($label)
    {
        $label = strtoupper($label);
        $this->writeCode(array(
            '@' . $label,
            '0;JMP'
        ));
    }

    /**
     * Traduzir if-goto $label
     */
    public function writeIf($label)
    {
        $this->pop('R13');
        $label = strtoupper($label);
        $this->writeCode(array(
            '@R13',
            'D=M',
            '@' . $label,
            'D;JNE'
        ));
    }

    /**
     * Traduzir declaração de função "function $functionName $numLocals"
     */
    public function writeFunction($functionName, $numLocals)
    {
        $this->writeCode(array(
            "($functionName)"
        ));
        for ($i=0; $i < $numLocals; $i++) {
            $this->writeCode(array(
                '@' . $i,
                'D=A', // D = index
                '@LCL',
                'A=D+M', // A = index + segment base
                'M=0' // local[$i] = 0
            ));
        }
    }

    public function writeReturn()
    {
        $setBaseFromFrame = function ($index, $dest) {
            $this->writeCode(array(
                '@' . $index,
                'D=A', // D = index
                '@R5', // R5 := frame
                'A=M-D', // A = index + segment base
                'D=M', // D = *(frame - $index)
                '@' . $dest,
                'M=D',
            ));
        };

        // R5-R12: temp vars
        $this->writeCode(array(
            '// @return',
            '@LCL',
            'D=M',
            '@R5', // R5 = frame
            'M=D', // frame = LCL
            // *ARG = pop
            '@SP',
            'A=M-1',
            'D=M',
            '@ARG',
            'A=M',
            'M=D',
            '@SP',
            'M=M-1',
            // restaurar @SP: @SP = ARG+1
            '@ARG',
            'A=M+1',
            'D=A',
            '@SP',
            'M=D',
        ));

        // Restaurar ponteiros base do caller
        // Relativo ao quadro atual: *(frame - N)
        $setBaseFromFrame(1, 'THAT');
        $setBaseFromFrame(2, 'THIS');
        $setBaseFromFrame(3, 'ARG');
        $setBaseFromFrame(4, 'LCL');
        $setBaseFromFrame(5, 'R6'); // R6: retAddr

        $this->writeCode(array(
            // goto retAddr
            '@R6',
            'A=M',
            '0;JMP'
        ));
    }

    public function writeCall($functionName, $numArgs)
    {
        $retAddr = 'returnAddress' . rand();
        $this->writeCode(array(
            '@' . $retAddr,
            'D=A', // a = retAddr
        ));
        $this->pushD(); // push returnAddress

        $this->writeCode(array(
            '@LCL',
            'D=M',
        ));
        $this->pushD(); // push LCL

        $this->writeCode(array(
            '@ARG',
            'D=M',
        ));
        $this->pushD(); // push ARG

        $this->writeCode(array(
            '@THIS',
            'D=M',
        ));
        $this->pushD(); // push THIS

        $this->writeCode(array(
            '@THAT',
            'D=M',
        ));
        $this->pushD(); // push THAT

        // arg = SP - nArgs - 5
        $this->writeCode(array(
            '@' . $numArgs,
            'D=A',
            '@SP',
            'D=M-D',
            '@5',
            'D=D-A',
            '@ARG',
            'M=D'
        ));
        // LCL = SP
        $this->writeCode(array(
            '@SP',
            'D=M',
            '@LCL',
            'M=D',
        ));

        // goto g, finally the jump
        $this->writeCode(array(
            '// call jump',
            '@' . $functionName,
            '0;JMP',
        ));

        $this->writeCode(array(
            "($retAddr)"
        ));
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
        if (is_resource($this->fp)) {
            fclose($this->fp);
        }
    }

    public function __destruct()
    {
        $this->close();
    }
}

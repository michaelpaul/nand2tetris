<?php

namespace VMTranslator;

class LogicOperator
{
    /**
     * Teste lógico baseado na diferença entre $x, $y e 0
     *
     * @param string $op operador lógico
     * @param string $x variavel/constante
     * @param string $y variavel/constante
     * @return array hack assembly
     */
    public function test($op, $x, $y)
    {
        $true = '-1';
        $false = '0';
        // @TODO gerar labels globalmente unicos  
        return array(
            '@' . $x,
            'D=M',
            '@' . $y,
            'D=D-M', // x - y
            '@FALSE',
            'D;' . $op, // if ($x - $y $op 0) goto f;
            '@r',
            'M=' . $true, // $r = true;
            '@END',
            '0;JMP', // goto end;
            '(FALSE)', // f:
            '@r',
            'M=' . $false, // $r = false;
            '(END)', // end:
            '@r',
            'D=M', // return $r;
        );
    }

    public function eq($x, $y)
    {
        return $this->test('JNE', $x, $y);
    }

    public function gt($x, $y)
    {
        return $this->test('JLE', $x, $y);
    }

    public function lt($x, $y)
    {
        return $this->test('JGE', $x, $y);
    }
}

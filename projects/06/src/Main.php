<?php

namespace Assembler;

class Main
{
    // @var SymbolTable
    private $st;
    private $base_address = 16;

    public function __construct()
    {
        $this->st = new SymbolTable;
    }

    public function firstPass($inputFile)
    {
        $p = new Parser($inputFile);
        $rom_address = 0;

        while ($p->hasMoreCommands()) {
            $p->advance();
            if ($p->commandType() == Parser::L_COMMAND) {
                $this->st->addEntry($p->symbol(), $rom_address);
            } else {
                $rom_address++;
            }
        }
    }

    public function getACommandValue($symbol)
    {
        // A register load with non-negative decimal
        if (ctype_digit($symbol)) {
            return $symbol;
        }

        // variable / label
        if ($this->st->contains($symbol)) {
            return $this->st->getAddress($symbol);
        } else {
            // new variable
            $a_val = $this->base_address;
            $this->st->addEntry($symbol, $this->base_address);
            $this->base_address++;
            return $a_val;
        }
    }

    public function getOutputFilename($inputFilename)
    {
        return preg_replace('/.asm$/', '.hack', $inputFilename);
    }

    public function assemble($inputFile)
    {
        // first pass
        $this->firstPass($inputFile);

        $p = new Parser($inputFile);
        $code = new Code;

        // second pass
        $out_fp = fopen($this->getOutputFilename($inputFile), 'w');

        while ($p->hasMoreCommands()) {
            $p->advance();
            switch ($p->commandType()) {
                case Parser::A_COMMAND:
                    $a_val = $this->getACommandValue($p->symbol());
                    // emit a command
                    fprintf($out_fp, "0%015b\n", $a_val);
                    break;
                case Parser::C_COMMAND:
                    $c_cmd = $code->comp($p->comp()) .
                        $code->dest($p->dest()) .
                        $code->jump($p->jump());
                    // emit c command
                    fprintf($out_fp, "111%s\n", $c_cmd);
                    break;
            }
        }

        fclose($out_fp);
    }
}

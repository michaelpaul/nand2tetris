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
            }
            $rom_address++;
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

    public function assemble($inputFile)
    {
        // first pass
        $this->firstPass($inputFile);

        $p = new Parser($inputFile);
        $code = new Code;

        // second pass

        while ($p->hasMoreCommands()) {
            $p->advance();
            switch ($p->commandType()) {
                case Parser::A_COMMAND:
                    $a_val = $this->getACommandValue($p->symbol());
                    printf("symbol? %s, val: %d \n", $p->symbol(), $a_val);
                    // emit a command
                    break;
                case Parser::C_COMMAND:
                    printf("%s = %s ; %s \n",
                        $code->dest($p->dest()),
                        $code->comp($p->comp()),
                        $code->jump($p->jump())
                    );
                    // emit c command
                    break;
            }
        }
    }
}

<?php

namespace Assembler;

class Main
{
    public function assemble($inputFile)
    {
        $p = new Parser($inputFile);
        $code = new Code;

        while ($p->hasMoreCommands()) {
            $p->advance();
            switch ($p->commandType()) {
                case Parser::A_COMMAND:
                case Parser::L_COMMAND:
                    printf("symbol? %s \n", $p->symbol());
                    break;
                case Parser::C_COMMAND:
                    printf("%s = %s ; %s \n",
                        $code->dest($p->dest()),
                        $code->comp($p->comp()),
                        $code->jump($p->jump())
                    );
                    break;
            }
        }
    }
}

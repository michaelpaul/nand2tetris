<?php

namespace VMTranslator;

class Main
{
    /**
     * @var CodeWriter
     */
    private $code;
    private $outfile;

    public function setOutputFilename($file)
    {
        $this->outfile = $file;
    }

    public function getOutputFilename()
    {
        if (!is_null($this->outfile)) {
            return $this->outfile;
        }
    }

    public function translate($inputFile)
    {
        $outfile = $this->getOutputFilename();
        $inputFiles = array($inputFile);

        if (is_file($inputFile) && $outfile == null) {
            $outfile = preg_replace('/.vm$/', '.asm', $inputFile);
        } else if (is_dir($inputFile)) {
            if ($outfile == null) {
                $outfile = $inputFile . '.asm';
            }
            $inputFiles = glob($inputFile . DIRECTORY_SEPARATOR . "*.vm");
        }

        $this->code = new CodeWriter($outfile);
        foreach ($inputFiles as $filename) {
            $this->translateFile($filename);
        }
        $this->code->close();
    }

    public function translateFile($inputFile)
    {
        $p = new Parser($inputFile);
        $this->code->setFilename($inputFile);

        while ($p->hasMoreCommands()) {
            $p->advance();
            switch ($p->commandType()) {
                case Parser::C_ARITHMETIC:
                    $this->code->writeArithmetic($p->arg1());
                    break;
                case Parser::C_PUSH:
                case Parser::C_POP:
                    $this->code->writePushPop($p->commandType(), $p->arg1(), $p->arg2());
                    break;
                case Parser::C_LABEL:
                    $this->code->writeLabel($p->arg1());
                    break;
                case Parser::C_IF:
                    $this->code->writeIf($p->arg1());
                    break;
                case Parser::C_GOTO:
                    $this->code->writeGoto($p->arg1());
                    break;
                case Parser::C_FUNCTION:
                    $this->code->writeFunction($p->arg1(), $p->arg2());
                    break;
                case Parser::C_RETURN:
                    $this->code->writeReturn();
                    break;
                case Parser::C_CALL:
                    $this->code->writeCall($p->arg1(), $p->arg2());
                    break;
                default:
                    throw new \Exception("Não foi possível traduzir o tipo: " . $p->commandTypeLabel());
            }
        }
    }
}

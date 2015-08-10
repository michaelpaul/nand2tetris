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
        // @TODO definir arquivo de saída
        return 'php://stdout';
    }

    public function translate($inputFile) {
        $this->code = new CodeWriter($this->getOutputFilename());
        if (is_file($inputFile)) {
            $this->translateFile($inputFile);
        } else if (is_dir($inputFile)) {
            foreach (glob($inputFile . DIRECTORY_SEPARATOR . "*.vm") as $filename) {
                $this->translateFile($filename);
            }
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
            }
        }
    }
}

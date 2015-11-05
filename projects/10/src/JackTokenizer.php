<?php

namespace JackCompiler;

class JackTokenizer
{
    private $fp;
    private $token;

    // token types
    const KEYWORD = 1;
    const SYMBOL = 2;
    const IDENTIFIER = 3;
    const INT_CONST = 4;
    const STRING_CONST = 5;

    /**
     * Opens the input file and gets ready to tokenize it
     */
    public function __construct($inputFile = null)
    {
        if (is_resource($inputFile)) {
            $this->fp = $inputFile;
        } else {
            if (!file_exists($inputFile)) {
                throw new \Exception("arquivo não encontrado: " . $inputFile);
            }
            $this->fp = fopen($inputFile, 'r');
        }
    }

    /**
     * Check input for more tokens
     * @return boolean
     */
    public function hasMoreTokens()
    {
        # code...
    }

    /**
     * Get next token and make it the current
     * @return void
     */
    public function advance()
    {
        # code...
    }

    /**
     * @return int type of the current token
     */
    public function tokenType()
    {
        # code...
    }

    /**
     * @return string the keyword which is the current token
     */
    public function keyword()
    {
        // jack keywords
        //  'class', 'method', 'function' 'constructor' 'int' 'boolean'
        // 'char' 'void' 'var' 'static' 'field' 'let' 'do' 'if' 'else'
        // 'while' 'return' 'true' 'false' 'null' 'this'
        # code...
    }

    /**
     * @return char current symbol
     */
    public function symbol()
    {
        # code...
    }

    /**
     * @return string current identifier
     */
    public function identifier()
    {
        # code...
    }

    /**
     * @return int16 the integer value
     */
    public function intVal()
    {
        # code...
    }

    /**
     * @return string the string value without double quotes
     */
    public function stringVal()
    {
        # code...
    }
}

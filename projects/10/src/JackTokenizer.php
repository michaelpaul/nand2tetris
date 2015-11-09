<?php

namespace JackCompiler;

class JackTokenizer
{
    private $fp;
    private $next = null;
    private $current = null;

    // token types
    const KEYWORD = 1;
    const SYMBOL = 2;
    const IDENTIFIER = 3;
    const INT_CONST = 4;
    const STRING_CONST = 5;

    private $keywords = array(
        'class', 'method', 'function', 'constructor', 'int', 'boolean',
        'char', 'void', 'var', 'static', 'field', 'let', 'do', 'if', 'else',
        'while', 'return', 'true', 'false', 'null', 'this'
    );

    private $symbols = array('(', ')', '{', '}', '<', '=', ';');

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
        $token = $this->tokenize();
        if ($token !== false) {
            $this->next = $token;
            return true;
        }
        return false;
    }

    /**
     * Get next token and make it the current
     * @return void
     */
    public function advance()
    {
        $this->current = $this->next;
    }

    private function tokenize()
    {
        $buffer = '';

        while (false !== ($c = fgetc($this->fp))) {
            // ignore whitespace
            if (ctype_space($c) || ctype_cntrl($c)) {
                continue;
            }
            // symbol
            if (in_array($c, $this->symbols)) {
                return $c;
            }

            $buffer = $c;
            // string
            if ($c == '"') {
                do {
                    $c = fgetc($this->fp);
                    $buffer .= $c;
                } while ($c != '"');
                return $buffer;
            }
            // word
            while (false !== ($c = fgetc($this->fp))) {
                // check end of word
                if (preg_match('/\W/', $c)) {
                    // unread this char, it may be a valid token
                    fseek($this->fp, -1, SEEK_CUR);
                    break;
                }
                $buffer .= $c;
            }
            return $buffer;
        }

        return false;
    }

    /**
     * @return int type of the current token
     */
    public function tokenType()
    {
        if (in_array($this->current, $this->keywords)) {
            return self::KEYWORD;
        } else if (in_array($this->current, $this->symbols)) {
            return self::SYMBOL;
        } else if (preg_match('/^[^\d]+\w+$/', $this->current)) {
            return self::IDENTIFIER;
        } else if (preg_match('/^\d+$/', $this->current)) {
            return self::INT_CONST;
        } else if (preg_match('/^"/', $this->current) &&
            preg_match('/"$/', $this->current)) {
            return self::STRING_CONST;
        }
    }

    /**
     * @return string the keyword which is the current token
     */
    public function keyword()
    {
        if (self::KEYWORD != $this->tokenType()) {
            return;
        }
        return $this->current;
    }

    /**
     * @return char current symbol
     */
    public function symbol()
    {
        if (self::SYMBOL != $this->tokenType()) {
            return;
        }
        return $this->current;
    }

    /**
     * @return string current identifier
     */
    public function identifier()
    {
        if (self::IDENTIFIER != $this->tokenType()) {
            return;
        }
        return $this->current;
    }

    /**
     * @return int16 the integer value
     */
    public function intVal()
    {
        if (self::INT_CONST != $this->tokenType()) {
            return;
        }
        return intval($this->current);
    }

    /**
     * @return string the string value without double quotes
     */
    public function stringVal()
    {
        # code...
    }
}

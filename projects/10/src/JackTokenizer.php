<?php

namespace JackCompiler;

class JackTokenizer
{
    // token types
    const KEYWORD = 1;
    const SYMBOL = 2;
    const IDENTIFIER = 3;
    const INT_CONST = 4;
    const STRING_CONST = 5;

    private $fp;
    private $next = null;
    private $current = null;
    private $keywords = array(
        'class', 'method', 'function', 'constructor', 'int', 'boolean',
        'char', 'void', 'var', 'static', 'field', 'let', 'do', 'if', 'else',
        'while', 'return', 'true', 'false', 'null', 'this'
    );
    private $symbols = array(
        '{', '}', '(', ')', '[', ']', '.',
        ',', ';', '+', '-', '*', '/', '&',
        '|', '<', '>', '=', '~'
    );

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

            // comment
            if ($c == '/') {
                $nextc = fgetc($this->fp);
                if ('/' == $nextc) {
                    $this->skipComment();
                    continue;
                } elseif ('*' == $nextc) {
                    $this->skipMultiLineComment();
                    continue;
                }
                fseek($this->fp, -1, SEEK_CUR);
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

    protected function skipComment()
    {
        while (false !== ($cc = fgetc($this->fp))) {
            if ($cc == "\n" || $cc == "\r") {
                break;
            }
        }
    }

    protected function skipMultiLineComment()
    {
        while (false !== ($cc = fgetc($this->fp))) {
            if ($cc == '*') {
                if ('/' == fgetc($this->fp)) {
                    break;
                }
                fseek($this->fp, -1, SEEK_CUR);
            }
        }
    }

    /**
     * @return int type of the current token
     */
    public function tokenType()
    {
        if (in_array($this->current, $this->keywords)) {
            return self::KEYWORD;
        } elseif (in_array($this->current, $this->symbols)) {
            return self::SYMBOL;
        } elseif (preg_match('/^[a-z]\w*$/i', $this->current)) {
            return self::IDENTIFIER;
        } elseif (preg_match('/^\d+$/', $this->current)) {
            return self::INT_CONST;
        } elseif (preg_match('/^"/', $this->current) &&
            preg_match('/"$/', $this->current)) {
            return self::STRING_CONST;
        } else {
            // throw new TokenizerError("Tipo inválido de token");
        }
    }

    public function typeLabel($type = null)
    {
        $type = $type ? $type : $this->tokenType();
        $labels = array(
            self::KEYWORD => 'keyword',
            self::SYMBOL => 'symbol',
            self::IDENTIFIER => 'identifier',
            self::INT_CONST => 'integerConstant',
            self::STRING_CONST => 'stringConstant',
        );
        return $labels[$type];
    }

    /**
     * @return string the keyword which is the current token
     */
    public function keyword()
    {
        if (self::KEYWORD != $this->tokenType()) {
            throw new TokenizerError('token atual não é uma keyword');
        }
        return $this->current;
    }

    public function keywordToken()
    {
        return new Token('keyword', $this->keyword());
    }

    /**
     * @param mixed $haystack variadic list of keywords to look for
     * @return bool true if current keyword is one of the arguments
     */
    public function isKeyword($haystack)
    {
        if (self::KEYWORD != $this->tokenType()) {
            return false;
        }
        return false !== array_search($this->keyword(), func_get_args());
    }
    
    /**
     * @return char current symbol
     */
    public function symbol()
    {
        if (self::SYMBOL != $this->tokenType()) {
            throw new TokenizerError('token atual não é um simbolo');
        }
        return $this->current;
    }
    
    public function symbolToken()
    {
        return new Token('symbol', htmlspecialchars($this->symbol(), ENT_XML1));
    }

    /**
     * @param mixed $haystack variadic list of symbols to look for
     * @return bool true if current symbol is one of the arguments
     */
    public function isSymbol($haystack)
    {
        if (self::SYMBOL != $this->tokenType()) {
            return false;
        }
        $symbols = is_array($haystack) ? $haystack : func_get_args();
        return false !== array_search($this->symbol(), $symbols);
    }
    
    /**
     * @return string current identifier
     */
    public function identifier()
    {
        if (self::IDENTIFIER != $this->tokenType()) {
            throw new TokenizerError('token atual não é um identificador');
        }
        return $this->current;
    }
    
    /**
     * @return bool true if current token is a identifier
     */
    public function isIdentifier()
    {
        return self::IDENTIFIER == $this->tokenType();
    }
    
    public function identifierToken()
    {
        return new Token('identifier', $this->identifier());
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

    public function intValToken()
    {
        return new Token('integerConstant', $this->intVal());
    }
    
    /**
     * @return bool true if current token is a integer constant
     */
    public function isInteger()
    {
        return self::INT_CONST == $this->tokenType();
    }
    
    /**
     * @return string the string value without double quotes
     */
    public function stringVal()
    {
        if (self::STRING_CONST != $this->tokenType()) {
            return;
        }
        return substr($this->current, 1, -1);
    }
    
    public function stringValToken()
    {
        return new Token('stringConstant', $this->stringVal());
    }
    
    /**
     * @return bool true if current token is a string constant
     */
    public function isString()
    {
        return self::STRING_CONST == $this->tokenType();
    }
}

<?php

namespace JackCompiler;

use JackCompiler\JackTokenizer;
use Exception;

class CompilationEngine
{
    protected $tokenizer;
    protected $output;
    /**
     * @var DOMDocument $doc documento xml para saída
     */
    protected $doc;
    /**
     * @var DOMElement $root a classe atual
     */
    protected $root;
    /**
     * @var DOMElement $ctx contexto atual da compilação
     */
    protected $ctx;

    /**
     * @param $input file/stream source.jack
     * @param $output stream resultado da compilação
     */
    public function __construct($input, $output)
    {
        $this->tokenizer = new JackTokenizer($input);
        $this->output = $output;
        $this->doc = new \DOMDocument();
        $this->doc->formatOutput = true;
        $this->doc->preserveWhiteSpace = false;
        // $this->root = $this->doc;
        $this->ctx = $this->doc;
    }

    public function getCtx()
    {
        return $this->ctx;
    }

    public function toXML()
    {
        $xml = $this->doc->saveXML();
        fwrite($this->output, $xml);
        return $xml;
    }

    public function advance()
    {
        if (!$this->tokenizer->hasMoreTokens()) {
            return; // @error
        }
        $this->tokenizer->advance();
    }
    
    protected function addKeyword()
    {
        $this->ctx->appendChild($this->doc->createElement('keyword',
            $this->tokenizer->keyword()));
    }

    protected function addIdentifier()
    {
        $this->ctx->appendChild($this->doc->createElement('identifier',
            $this->tokenizer->identifier()));
    }

    protected function addSymbol()
    {
        $this->ctx->appendChild($this->doc->createElement('symbol',
            $this->tokenizer->symbol()));
    }

    protected function checkType($type)
    {
        if ($this->tokenizer->tokenType() != $type) {
            $msg = 'Esperava "%s", encontrei "%s"';
            throw new Exception(sprintf($msg,
                $this->tokenizer->typeLabel($type),
                $this->tokenizer->typeLabel()));
        }
    }

    /** {{{ Lexical elements */
    // The Jack language includes five types of terminal elements (tokens) 
    
    protected function compileTerminalKeyword($val)
    {
        $this->checkType(JackTokenizer::KEYWORD);
        $found = $this->tokenizer->keyword();
        if ($found != $val) {
            throw new Exception("Esperava keyword \"$val\" encontrei \"$found\"");
        }
        $this->addKeyword();
        $this->advance();
    }

    protected function compileTerminalSymbol($val)
    {
        $this->checkType(JackTokenizer::SYMBOL);
        $found = $this->tokenizer->symbol();
        if ($found != $val) {
            throw new Exception("Esperava simbolo \"$val\" encontrei \"$found\"");
        }
        $this->addSymbol();
        $this->advance();
    }

    /** }}} */
    
    /** {{{ Grammar productions */

    // 'class' className '{' classVarDec* subroutineDec* '}'
    public function compileClass()
    {
        $this->advance();
        $this->root = $this->doc->appendChild($this->doc->createElement('class'));
        $this->ctx = $this->root;
        
        $this->compileTerminalKeyword('class');
        $this->compileClassName();
        $this->compileTerminalSymbol('{');
        
        while (in_array($this->tokenizer->keyword(), array('static', 'field'))) {
            $this->compileClassVarDec();
        }
        while (in_array($this->tokenizer->keyword(), array('constructor', 'function', 'method'))) {
            $this->compileSubroutine();
        }

        $this->advance();
        $this->compileTerminalSymbol('}');
    }

    // ('static' | 'field') type varName (',' varName)* ';'
    public function compileClassVarDec()
    {
        $this->ctx = $this->ctx->appendChild($this->doc->createElement('classVarDec'));

        $this->addKeyword();
        $this->advance();

        $this->compileType();
        $this->compileVarName();
        
        while ($this->tokenizer->symbol() == ',') {
            $this->addSymbol();
            $this->advance();
            $this->compileVarName();
        }

        if ($this->tokenizer->symbol() != ';') {
            return; // @error
        }

        $this->addSymbol();
        $this->advance();
        $this->ctx = $this->ctx->parentNode;
    }

    // 'int' | 'char' | 'boolean' | className
    protected function compileType()
    {
        if (in_array($this->tokenizer->keyword(), array('int', 'char', 'boolean'))) {
            $this->compileTerminalKeyword($this->tokenizer->keyword());
        } else {
            $this->compileClassName();
        }
    }

    // ('constructor' | 'function' | 'method') ('void' | type) subroutineName '(' parameterList ')' subroutineBody
    public function compileSubroutine()
    {
        $this->ctx = $this->ctx->appendChild($this->doc->createElement('subroutineDec'));
        $this->addKeyword();
        $this->advance();

        if ($this->tokenizer->keyword() == 'void') {
            $this->addKeyword();
            $this->advance();
        } else {
            $this->compileType();
        }

        $this->compileSubroutineName();
        $this->advance();

        if ($this->tokenizer->symbol() != '(') {
            return; // @error
        }

        $this->addSymbol();
        $this->advance();
        $this->compileParameterList();

        if ($this->tokenizer->symbol() != ')') {
            return; // @error
        }

        $this->addSymbol();
        $this->advance();
        $this->compileSubroutineBody();
        $this->ctx = $this->ctx->parentNode;
    }

    // ((type varName) (',' type varName)*)?
    public function compileParameterList()
    {
        $this->ctx = $this->ctx->appendChild($this->doc->createElement('parameterList'));

        // sem parametros
        if ($this->tokenizer->keyword() == null && $this->tokenizer->identifier() == null) {
            // $this->ctx->appendChild($this->doc->createTextNode(''));
            $this->ctx = $this->ctx->parentNode;
            return true;
        }

        $this->compileType();
        $this->compileVarName();
        
        while ($this->tokenizer->symbol() == ',') {
            $this->addSymbol();
            $this->advance();

            $this->compileType();
            $this->compileVarName();
        }

        $this->ctx = $this->ctx->parentNode;
    }

    // '{' varDec* statements '}'
    protected function compileSubroutineBody()
    {
        $this->ctx = $this->ctx->appendChild($this->doc->createElement('subroutineBody'));

        if ($this->tokenizer->symbol() != '{') {
            return; // @error
        }

        $this->addSymbol();
        $this->advance();

        while ($this->tokenizer->keyword() == 'var') {
            $this->compileVarDec();
        }

        $this->advance();
        $this->compileStatements();

        $this->advance();
        if ($this->tokenizer->symbol() != '}') {
            return; // @error
        }
        $this->addSymbol();
    }

    // 'var' type varName (',' varName)* ';'
    public function compileVarDec()
    {
        $this->ctx = $this->ctx->appendChild($this->doc->createElement('varDec'));
        $this->compileTerminalKeyword('var');
        $this->compileType();
        $this->compileVarName();
        while ($this->tokenizer->symbol() == ',') {
            $this->compileTerminalSymbol(',');
            $this->compileVarName();
        }
        $this->compileTerminalSymbol(';');
        $this->ctx = $this->ctx->parentNode;
    }
    
    // identifier
    protected function compileClassName()
    {
        $this->checkType(JackTokenizer::IDENTIFIER);
        $this->addIdentifier();
        $this->advance();
    }
    // identifier
    protected function compileSubroutineName()
    {
        $this->checkType(JackTokenizer::IDENTIFIER);
        $this->addIdentifier();
    }
    // identifier
    protected function compileVarName()
    {
        $this->checkType(JackTokenizer::IDENTIFIER);
        $this->addIdentifier();
        $this->advance();
    }
    
    // statement*
    public function compileStatements()
    {
        # code...
    }

    // 'do' subroutineCall ';'
    public function compileDo()
    {
        # code...
    }

    // 'let' varName ('[' expression ']')? '=' expression ';'
    public function compileLet()
    {
        # code...
    }

    // 'while' '(' expression ')' '{' statements '}'
    public function compileWhile()
    {
        # code...
    }

    // 'return' expression? ';'
    public function compileReturn()
    {
        # code...
    }

    // 'if' '(' expression ')' '{' statements '}' ('else' '{' statements '}')?
    public function compileIf()
    {
        # code...
    }

    public function compileExpression()
    {
        # code...
    }

    public function compileTerm()
    {
        # code...
    }

    public function compileExpressionList()
    {
        # code...
    }
    /** }}} */
}

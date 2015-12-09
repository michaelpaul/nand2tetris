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

    /** {{{ Rule parsers */
    
    // 'class' className '{' classVarDec* subroutineDec* '}'
    public function compileClass()
    {
        $this->advance();
        $this->checkType(JackTokenizer::KEYWORD);
        if ($this->tokenizer->keyword() != 'class') {
            return; // @error
        }
        $this->root = $this->doc->appendChild($this->doc->createElement('class'));
        $this->ctx = $this->root;

        $this->addKeyword();
        $this->advance();
        $this->checkType(JackTokenizer::IDENTIFIER);
        $this->addIdentifier();
        $this->advance();
        $this->checkType(JackTokenizer::SYMBOL);
        $this->addSymbol();

        $this->advance();

        while (in_array($this->tokenizer->keyword(), array('static', 'field'))) {
            $this->compileClassVarDec();
        }
        while (in_array($this->tokenizer->keyword(), array('constructor', 'function', 'method'))) {
            $this->compileSubroutine();
        }

        $this->advance();
        
        if ($this->tokenizer->symbol() !== '}') {
            return; // @error
        }
        $this->addSymbol();
    }
    
    // ('static' | 'field') type varName (',' varName)* ';'
    public function compileClassVarDec()
    {
        $this->ctx = $this->ctx->appendChild($this->doc->createElement('classVarDec'));

        $this->addKeyword();
        $this->advance();

        $this->compileType();
        
        $this->compileVarName();
        $this->advance();
        
        while ($this->tokenizer->symbol() == ',') {
            $this->addSymbol();
            $this->advance();
            $this->compileVarName();
            $this->advance();
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
            $this->addKeyword();
        } else {
            $this->checkType(JackTokenizer::IDENTIFIER);
            $this->addIdentifier();
        }
        $this->advance();
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
        $this->ctx = $this->root;
    }

    // ((type varName) (',' type varName)*)?
    public function compileParameterList()
    {
        // sem parametros
        if ($this->tokenizer->symbol() == ')') {
            return true;
        }

        $this->ctx = $this->ctx->appendChild($this->doc->createElement('parameterList'));
        $this->compileType();
        $this->compileVarName();
        $this->advance();

        while ($this->tokenizer->symbol() == ',') {
            $this->addSymbol();
            $this->advance();

            $this->compileType();
            $this->compileVarName();
            $this->advance();
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
    }

    // 'var' type varName (',' varName)* ';'
    public function compileVarDec()
    {
        # code...
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

    // identifier
    protected function compileClassName()
    {
        $this->checkType(JackTokenizer::IDENTIFIER);
        $this->addIdentifier();
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
    }
    
    /** {{{ Helpers */
    
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
    /** }}} */
}

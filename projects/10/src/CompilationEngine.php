<?php

namespace JackCompiler;

use JackCompiler\JackTokenizer;
use Exception;

class CompilationEngine
{
    protected $tokenizer;
    protected $ctx;
    protected $root;
    protected $doc;
    protected $output;

    function __construct(JackTokenizer $input, $output)
    {
        $this->tokenizer = $input;
        $this->output = $output;
        $this->doc = new \DOMDocument();
        $this->doc->formatOutput = true;
    }

    // 'class' className '{' classVarDec* subroutineDec* '}'
    public function compileClass()
    {
        // move
        $this->advance();
        $this->checkType(JackTokenizer::KEYWORD);
        if ($this->tokenizer->keyword() != 'class') {
            return;
        }
        $this->root = $this->doc->appendChild($this->doc->createElement('class'));
        $this->ctx = $this->root;

        $this->addKeyword();
        // move
        $this->advance();
        $this->checkType(JackTokenizer::IDENTIFIER);
        $this->addIdentifier();
        $this->advance();
        $this->checkType(JackTokenizer::SYMBOL);
        $this->addSymbol();

        $this->advance();

        while(in_array($this->tokenizer->keyword(), array('static', 'field'))) {
            $this->compileClassVarDec();
        }
        while(in_array($this->tokenizer->keyword(), array('constructor', 'function', 'method'))) {
            $this->compileSubroutine();
        }

        // if ($this->tokenizer->symbol() !== '}') {
        //     return;
        // }

        // end
        $this->doc->save($this->output);
    }

    protected function advance()
    {
        // move
        if (!$this->tokenizer->hasMoreTokens()) {
            return;
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

    // ('static' | 'field') type varName (',' varName)* ';'
    public function compileClassVarDec()
    {
        $this->ctx = $this->ctx->appendChild($this->doc->createElement('classVarDec'));

        $this->addKeyword();
        $this->advance();

        $this->compileType();
        $this->advance();
        // group
        $this->compileVarName();
        $this->advance();
        // opt
        if ($this->tokenizer->symbol() == ',') {
            $this->addSymbol();
            $this->advance();
            $this->compileVarName();
            $this->advance();
        }

        if ($this->tokenizer->symbol() != ';') {
            return;
        }

        $this->addSymbol();
        $this->advance();
        $this->ctx = $this->root;
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
    }

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

    // ('constructor' | 'function' | 'method') ('void' | type) subroutineName '(' parameterList ')' subroutineBody
    public function compileSubroutine()
    {
        $this->ctx = $this->ctx->appendChild($this->doc->createElement('subroutineDec'));
        $this->addKeyword();
        $this->advance();
        if ($this->tokenizer->keyword() == 'void') {
            $this->addKeyword();
        } else {
            $this->compileType();
        }
        $this->advance();
        $this->compileSubroutineName();
        $this->advance();

        if ($this->tokenizer->symbol() != '(') {
            return;
        }

        $this->addSymbol();
        $this->advance();
        $this->compileParameterList();

        if ($this->tokenizer->symbol() != ')') {
            return;
        }

        $this->addSymbol();
        $this->advance();
        $this->compileSubroutineBody();
        $this->ctx = $this->root;
    }

    // '{' varDec* statements '}'
    protected function compileSubroutineBody()
    {
        $this->ctx = $this->ctx->appendChild($this->doc->createElement('subroutineBody'));

        if ($this->tokenizer->symbol() != '{') {
            return;
        }
        $this->advance();
        while($this->tokenizer->keyword() == 'var') {
            $this->compileVarDec();
        }

        $this->advance();
        $this->compileStatements();

        $this->advance();
        if ($this->tokenizer->symbol() != '}') {
            return;
        }
    }

    // ((type varName) (',' type varName)*)?
    public function compileParameterList()
    {
        if ($this->tokenizer->symbol() == ')') {
            return true;
        }

        $this->ctx = $this->ctx->appendChild($this->doc->createElement('parameterList'));
        $this->compileType();
        $this->advance();
        $this->compileVarName();
        $this->advance();

        while ($this->tokenizer->symbol() == ',') {
            $this->addSymbol();
            $this->advance();

            $this->compileType();
            $this->advance();
            $this->compileVarName();
            $this->advance();
        }

        $this->ctx = $this->ctx->parentNode;
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
}

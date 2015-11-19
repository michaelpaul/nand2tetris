<?php

namespace JackCompiler;

use JackCompiler\JackTokenizer;
use Exception;

class CompilationEngine
{
    protected $tokenizer;
    protected $ctx;
    protected $root;
    protected $output;

    function __construct(JackTokenizer $input, $output)
    {
        $this->tokenizer = $input;
        $this->output = $output;
        $this->root = new \DOMDocument();
        $this->root->formatOutput = true;
        $this->ctx = $this->root;
    }

    public function compileClass()
    {
        // move
        $this->advance();
        $this->checkType(JackTokenizer::KEYWORD);
        if ($this->tokenizer->keyword() != 'class') {
            return;
        }
        $this->ctx = $this->ctx->appendChild($this->ctx->createElement('class'));
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
        $this->root->save($this->output);
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
        $this->ctx->appendChild($this->root->createElement('keyword',
            $this->tokenizer->keyword()));
    }

    protected function addIdentifier()
    {
        $this->ctx->appendChild($this->root->createElement('identifier',
            $this->tokenizer->identifier()));
    }

    protected function addSymbol()
    {
        $this->ctx->appendChild($this->root->createElement('symbol',
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

    public function compileClassVarDec()
    {
        $this->ctx = $this->ctx->appendChild($this->root->createElement('classVarDec'));
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
        $this->ctx = $this->ctx->parentNode;
    }

    protected function compileType()
    {
        if (in_array($this->tokenizer->keyword(), array('int', 'char', 'boolean'))) {
            $this->addKeyword();
        } else {
            $this->checkType(JackTokenizer::IDENTIFIER);
            $this->addIdentifier();
        }
    }

    protected function compileVarName()
    {
        $this->checkType(JackTokenizer::IDENTIFIER);
        $this->addIdentifier();
    }

    public function compileSubroutine()
    {
        $this->ctx = $this->ctx->appendChild($this->root->createElement('subroutineDec'));
        $this->addKeyword();
        $this->advance();
        if ($this->tokenizer->keyword() == 'void') {
            $this->addKeyword();
        } else {
            $this->compileType();
        }
        $this->advance();
    }

    public function compileParameterList()
    {
        # code...
    }

    public function compileVarDec()
    {
        # code...
    }

    public function compileStatements()
    {
        # code...
    }

    public function compileDo()
    {
        # code...
    }

    public function compileLet()
    {
        # code...
    }

    public function compileWhile()
    {
        # code...
    }

    public function compileReturn()
    {
        # code...
    }

    public function compileIf()
    {
        # code...
    }
}

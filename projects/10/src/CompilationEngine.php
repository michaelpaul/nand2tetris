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
    
    /** {{{ Lexical elements */
    // The Jack language includes five types of terminal elements (tokens) 

    public function compileTerminalKeyword($val)
    {
        $isKeyword = array($this->tokenizer, 'isKeyword');
        $keywords = func_get_args();
        if (! call_user_func_array($isKeyword, $keywords)) {
            throw new ParserError("Esperava keyword ( '" . implode("' | '", $keywords) . "' )");
        }
        $this->addTerminal($this->tokenizer->keywordToken());
        $this->advance();
    }

    public function compileTerminalSymbol($val)
    {
        $isSymbol = array($this->tokenizer, 'isSymbol');
        $symbols = func_get_args();
        if (! call_user_func_array($isSymbol, $symbols)) {
            throw new ParserError("Esperava simbolo ( '" . implode("' | '", $symbols) . "' )");
        }
        $this->addTerminal($this->tokenizer->symbolToken());
        $this->advance();
    }
    
    protected function addTerminal(Token $token)
    {
        $this->ctx->appendChild($this->doc->createElement($token->type, $token->val));
    }

    protected function identifier()
    {
        $this->addTerminal($this->tokenizer->identifierToken());
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
        $this->identifier();
        $this->compileTerminalSymbol('{');
        
        while ($this->tokenizer->isKeyword('static', 'field')) {
            $this->compileClassVarDec();
        }
        while ($this->tokenizer->isKeyword('constructor', 'function', 'method')) {
            $this->compileSubroutine();
        }

        $this->compileTerminalSymbol('}');
    }

    // ('static' | 'field') type varName (',' varName)* ';'
    public function compileClassVarDec()
    {
        $this->ctx = $this->ctx->appendChild($this->doc->createElement('classVarDec'));

        $this->compileTerminalKeyword('static', 'field');
        $this->compileType();
        $this->identifier();
        
        while ($this->tokenizer->isSymbol(',')) {
            $this->compileTerminalSymbol(',');
            $this->identifier();
        }

        $this->compileTerminalSymbol(';');
        $this->ctx = $this->ctx->parentNode;
    }

    // 'int' | 'char' | 'boolean' | className
    protected function compileType()
    {
        if ($this->tokenizer->isKeyword('int', 'char', 'boolean')) {
            $this->compileTerminalKeyword($this->tokenizer->keyword());
        } else {
            $this->identifier();
        }
    }

    // ('constructor' | 'function' | 'method') ('void' | type) subroutineName '(' parameterList ')' subroutineBody
    public function compileSubroutine()
    {
        $this->ctx = $this->ctx->appendChild($this->doc->createElement('subroutineDec'));
        
        $this->compileTerminalKeyword('constructor', 'function', 'method');
        
        if ($this->tokenizer->isKeyword('void')) {
            $this->compileTerminalKeyword('void');
        } else {
            $this->compileType();
        }

        $this->identifier();
        $this->compileTerminalSymbol('(');
        $this->compileParameterList();
        $this->compileTerminalSymbol(')');
        $this->compileSubroutineBody();
        $this->ctx = $this->ctx->parentNode;
    }

    // ((type varName) (',' type varName)*)?
    public function compileParameterList()
    {
        $this->ctx = $this->ctx->appendChild($this->doc->createElement('parameterList'));

        // sem parametros
        if ($this->tokenizer->tokenType() != JackTokenizer::KEYWORD &&
            ! $this->tokenizer->isIdentifier()) {
            // $this->ctx->appendChild($this->doc->createTextNode(''));
            $this->ctx = $this->ctx->parentNode;
            return true;
        }

        $this->compileType();
        $this->identifier();
        
        while ($this->tokenizer->isSymbol(',')) {
            $this->compileTerminalSymbol(',');
            $this->compileType();
            $this->identifier();
        }

        $this->ctx = $this->ctx->parentNode;
    }

    // '{' varDec* statements '}'
    public function compileSubroutineBody()
    {
        $this->ctx = $this->ctx->appendChild($this->doc->createElement('subroutineBody'));

        $this->compileTerminalSymbol('{');

        while ($this->tokenizer->isKeyword('var')) {
            $this->compileVarDec();
        }
        
        $this->compileStatements();
        $this->compileTerminalSymbol('}');
    }

    // 'var' type varName (',' varName)* ';'
    public function compileVarDec()
    {
        $this->ctx = $this->ctx->appendChild($this->doc->createElement('varDec'));
        $this->compileTerminalKeyword('var');
        $this->compileType();
        $this->identifier();
        
        while ($this->tokenizer->isSymbol(',')) {
            $this->compileTerminalSymbol(',');
            $this->identifier();
        }
        $this->compileTerminalSymbol(';');
        $this->ctx = $this->ctx->parentNode;
    }
    
    /** Statements **/ 
    
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
        $this->ctx = $this->ctx->appendChild($this->doc->createElement('letStatement'));
        $this->compileTerminalKeyword('let');
        $this->identifier();
        if ($this->tokenizer->isSymbol('[')) {
            $this->compileTerminalSymbol('[');
            $this->compileExpression();
            $this->compileTerminalSymbol(']');
        }
        $this->compileTerminalSymbol('=');
        $this->compileExpression();
        $this->compileTerminalSymbol(';');
        $this->ctx = $this->ctx->parentNode;
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

    /** Expressions **/ 

    // term (op term)*
    public function compileExpression()
    {
        $this->ctx = $this->ctx->appendChild($this->doc->createElement('expression'));
        $this->compileTerm();
        $this->ctx = $this->ctx->parentNode;
    }

    // integerConstant | stringConstant | keywordConstant | varName | 
    // varName '[' expression ']' | subroutineCall | '(' expression ')' | 
    // unaryOp term
    public function compileTerm()
    {
        $this->ctx = $this->ctx->appendChild($this->doc->createElement('term'));
        $this->identifier();
        $this->ctx = $this->ctx->parentNode;
    }

    // (expression (',' expression)*)?
    public function compileExpressionList()
    {
        # code...
    }
    /** }}} */
}

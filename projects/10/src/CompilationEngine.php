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
    
    /**
     * Start parsing a non-terminal $element
     */
    public function beginElement($element)
    {
        $this->ctx = $this->ctx->appendChild($this->doc->createElement($element));
    }
    /**
     * Parsing done for a non-terminal element
     */
    public function endElement()
    {
        $this->ctx = $this->ctx->parentNode;
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
        $symbols = is_array($val) ? $val : func_get_args();
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
        $this->beginElement('classVarDec');
        $this->compileTerminalKeyword('static', 'field');
        $this->compileType();
        $this->identifier();
        
        while ($this->tokenizer->isSymbol(',')) {
            $this->compileTerminalSymbol(',');
            $this->identifier();
        }

        $this->compileTerminalSymbol(';');
        $this->endElement();
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
        $this->beginElement('subroutineDec');
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
        $this->endElement();
    }

    // ((type varName) (',' type varName)*)?
    public function compileParameterList()
    {
        $this->beginElement('parameterList');

        // sem parametros
        if ($this->tokenizer->tokenType() != JackTokenizer::KEYWORD &&
            ! $this->tokenizer->isIdentifier()) {
            // $this->ctx->appendChild($this->doc->createTextNode(''));
            $this->endElement();
            return true;
        }

        $this->compileType();
        $this->identifier();
        
        while ($this->tokenizer->isSymbol(',')) {
            $this->compileTerminalSymbol(',');
            $this->compileType();
            $this->identifier();
        }

        $this->endElement();
    }

    // '{' varDec* statements '}'
    public function compileSubroutineBody()
    {
        $this->beginElement('subroutineBody');
        $this->compileTerminalSymbol('{');

        while ($this->tokenizer->isKeyword('var')) {
            $this->compileVarDec();
        }
        
        $this->compileStatements();
        $this->compileTerminalSymbol('}');
        $this->endElement();
    }

    // 'var' type varName (',' varName)* ';'
    public function compileVarDec()
    {
        $this->beginElement('varDec');
        $this->compileTerminalKeyword('var');
        $this->compileType();
        $this->identifier();
        
        while ($this->tokenizer->isSymbol(',')) {
            $this->compileTerminalSymbol(',');
            $this->identifier();
        }
        
        $this->compileTerminalSymbol(';');
        $this->endElement();
    }
    
    /** Statements **/
    
    // statements: statement*
    // statement: letStatement | ifStatement | whileStatement | doStatement | returnStatement
    public function compileStatements()
    {
        $this->beginElement('statements');
        
        while ($this->tokenizer->isKeyword('let', 'if', 'while', 'do', 'return')) {
            if ($this->tokenizer->isKeyword('let')) {
                $this->compileLet();
            } elseif ($this->tokenizer->isKeyword('if')) {
                $this->compileIf();
            } elseif ($this->tokenizer->isKeyword('while')) {
                $this->compileWhile();
            } elseif ($this->tokenizer->isKeyword('do')) {
                $this->compileDo();
            } elseif ($this->tokenizer->isKeyword('return')) {
                $this->compileReturn();
            } else {
                throw new ParserError("Statement não suportado '" . $this->tokenizer->keyword() . "'");
            }
        }
        
        $this->endElement();
    }

    // 'do' subroutineCall ';'
    public function compileDo()
    {
        $this->beginElement('doStatement');
        $this->compileTerminalKeyword('do');
        $this->compileSubroutineCall();
        $this->compileTerminalSymbol(';');
        $this->endElement();
    }

    // 'let' varName ('[' expression ']')? '=' expression ';'
    public function compileLet()
    {
        $this->beginElement('letStatement');
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
        $this->endElement();
    }

    // 'while' '(' expression ')' '{' statements '}'
    public function compileWhile()
    {
        $this->beginElement('whileStatement');
        $this->compileTerminalKeyword('while');
        $this->compileTerminalSymbol('(');
        $this->compileExpression();
        $this->compileTerminalSymbol(')');
        $this->compileTerminalSymbol('{');
        $this->compileStatements();
        $this->compileTerminalSymbol('}');
        $this->endElement();
    }

    // 'return' expression? ';'
    public function compileReturn()
    {
        $this->beginElement('returnStatement');
        $this->compileTerminalKeyword('return');
        
        if (! $this->tokenizer->isSymbol(';')) {
            $this->compileExpression();
        }
        
        $this->compileTerminalSymbol(';');
        $this->endElement();
    }

    // 'if' '(' expression ')' '{' statements '}' ('else' '{' statements '}')?
    public function compileIf()
    {
        $this->beginElement('ifStatement');
        $this->compileTerminalKeyword('if');
        $this->compileTerminalSymbol('(');
        $this->compileExpression();
        $this->compileTerminalSymbol(')');
        $this->compileTerminalSymbol('{');
        $this->compileStatements();
        $this->compileTerminalSymbol('}');
        
        if ($this->tokenizer->isKeyword('else')) {
            $this->compileTerminalKeyword('else');
            $this->compileTerminalSymbol('{');
            $this->compileStatements();
            $this->compileTerminalSymbol('}');
        }
        
        $this->endElement();
    }

    // subroutineName '(' expressionList ')' | 
    //  (className | varName) '.' subroutineName '(' expressionList ')'
    protected function compileSubroutineCall()
    {
        $this->identifier();
        
        if ($this->tokenizer->isSymbol('.')) {
            $this->compileTerminalSymbol('.');
            $this->identifier();
        }
        
        $this->compileTerminalSymbol('(');
        $this->compileExpressionList();
        $this->compileTerminalSymbol(')');
    }
    
    /** Expressions **/

    // term (op term)*
    public function compileExpression()
    {
        $this->beginElement('expression');
        $this->compileTerm();
        
        $op = array('+', '-', '*', '/', '&', '|', '<', '>', '=');
        
        while ($this->tokenizer->isSymbol($op)) {
            $this->compileTerminalSymbol($op);
            $this->compileTerm();
        }
        
        $this->endElement();
    }
    
    // keywordConstant: 'true' | 'false' | 'null' | 'this'

    // term: integerConstant | stringConstant | keywordConstant | varName | 
    // varName '[' expression ']' | subroutineCall | '(' expression ')' | 
    // unaryOp term
    public function compileTerm()
    {
        $this->beginElement('term');
        if ($this->tokenizer->isInteger()) {
            $this->addTerminal($this->tokenizer->intValToken());
            $this->advance();
        } elseif ($this->tokenizer->isString()) {
            $this->addTerminal($this->tokenizer->stringValToken());
            $this->advance();
        } elseif ($this->tokenizer->isKeyword('true', 'false', 'null', 'this')) {
            $this->compileTerminalKeyword('true', 'false', 'null', 'this');
        } else {
            $this->identifier();
        }
        $this->endElement();
    }

    // (expression (',' expression)*)?
    public function compileExpressionList()
    {
        $this->beginElement('expressionList');
        // empty expression
        if ($this->tokenizer->isSymbol(')') || ! $this->tokenizer->tokenType()) {
            $this->endElement();
            return;
        }
        
        $this->compileExpression();
        
        while ($this->tokenizer->isSymbol(',')) {
            $this->compileTerminalSymbol(',');
            $this->compileExpression();
        }
        
        $this->endElement();
    }
    /** }}} */
}

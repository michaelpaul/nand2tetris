<?php

namespace JackCompiler;

use JackCompiler\JackTokenizer;
use Exception;

class CompilationEngine
{
    // variáveis temporárias da VM usadas pelo compilador
    const TEMP_DO_RETURN = 0;
    const TEMP_ARRAY_INDEX = 1;
    
    protected $tokenizer;
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
     * @var SymbolTable $st
     */
    protected $st;
    /**
     * @var VMWriter $writer
     */
    protected $writer;
    /**
     * Classe sendo compilada
     */
    protected $className;
    
    protected $labelCounter = 0;
    
    /**
     * @param $input file/stream source.jack
     */
    public function __construct($input)
    {
        $this->tokenizer = new JackTokenizer($input);
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
    
    public function setSymbolTable(SymbolTable $st)
    {
        $this->st = $st;
    }
    
    public function getSymbolTable()
    {
        return $this->st;
    }

    public function toXML($xmlFilename)
    {
        $this->doc->save($xmlFilename);
    }
    
    public function toSimpleXML()
    {
        return simplexml_import_dom($this->ctx, 'SimpleXMLIterator');
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
    
    public function setWriter(VMWriter $writer)
    {
        $this->writer = $writer;
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
        $identifier = $this->tokenizer->identifier();
        $this->addTerminal($this->tokenizer->identifierToken());
        $this->advance();
        return $identifier;
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
        $this->className = $this->identifier();
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
        $type = $this->compileType();
        $id = $this->identifier();
        
        $kind = $this->ctx->firstChild->nodeValue;
        $this->st->define($id, $type, $kind);
        
        while ($this->tokenizer->isSymbol(',')) {
            $this->compileTerminalSymbol(',');
            $id = $this->identifier();
            $this->st->define($id, $type, $kind);
        }

        $this->compileTerminalSymbol(';');
        $this->endElement();
    }

    // 'int' | 'char' | 'boolean' | className
    protected function compileType()
    {
        $type = null;
        if ($this->tokenizer->isKeyword('int', 'char', 'boolean')) {
            $type = $this->tokenizer->keyword();
            $this->compileTerminalKeyword($this->tokenizer->keyword());
        } else {
            $type = $this->tokenizer->identifier();
            $this->identifier();
        }
        return $type;
    }

    // ('constructor' | 'function' | 'method') ('void' | type) subroutineName '(' parameterList ')' subroutineBody
    public function compileSubroutine()
    {
        $this->beginElement('subroutineDec');
        $this->st->startSubroutine();
        $this->compileTerminalKeyword('constructor', 'function', 'method');
        
        $voidSubroutine = false;
        if ($this->tokenizer->isKeyword('void')) {
            $this->compileTerminalKeyword('void');
            $voidSubroutine = true;
        } else {
            $this->compileType();
        }

        $subroutineName = $this->identifier();
        $this->compileTerminalSymbol('(');
        $this->compileParameterList();
        $this->compileTerminalSymbol(')');
        $this->compileSubroutineBody($subroutineName);
        $this->checkReturn($subroutineName, $voidSubroutine);
        $this->endElement();
    }
    
    // Some dumb checks
    protected function checkReturn($subroutineName, $voidSubroutine)
    {
        $subroutineKind = $this->ctx->firstChild->nodeValue;
        if ($subroutineKind == 'constructor') {
            // verificar o retorno de this no topo da função 
            $returnStatements = $this->toSimpleXML()->subroutineBody->statements->returnStatement;
            $thisFound = false;
            foreach ($returnStatements as $returnNode) {
                if ($returnNode->expression || $returnNode->expression->term->keyword == 'this') {
                    $thisFound = true;
                    break;
                }
            }
            if (!$thisFound) {
                throw new ParserError("Construtor {$this->className}.{$subroutineName} deve retornar this incondicional");
            }
            return true;
        }
        
        $xpath = new \DOMXPath($this->doc);
        $returnStatements = $xpath->query('//returnStatement', $this->ctx);
        
        if ($returnStatements->length == 0) {
            if ($voidSubroutine) {
                // return 0
                $this->writer->writePush('constant', 0);
                $this->writer->writeReturn();
            } else {
                throw new ParserError("Sub-rotina sem retorno: {$this->className}.{$subroutineName}");
            }
        }
        
        return true;
    }
    
    // ((type varName) (',' type varName)*)?
    public function compileParameterList()
    {
        $this->beginElement('parameterList');

        // adicionar variável this na tabela de símbolos 
        $methodKind = $this->ctx->parentNode->firstChild->nodeValue;
        if ($methodKind == 'method') {
            $this->st->define('this', $this->className, 'arg');
        }
        
        // sem parâmetros
        if ($this->tokenizer->tokenType() != JackTokenizer::KEYWORD &&
            ! $this->tokenizer->isIdentifier()) {
            $this->endElement();
            return true;
        }

        $type = $this->compileType();
        $id = $this->identifier();
        $this->st->define($id, $type, 'arg');
        
        while ($this->tokenizer->isSymbol(',')) {
            $this->compileTerminalSymbol(',');
            $type = $this->compileType();
            $id = $this->identifier();
            $this->st->define($id, $type, 'arg');
        }

        $this->endElement();
    }

    // '{' varDec* statements '}'
    public function compileSubroutineBody($subroutineName)
    {
        $nLocals = 0;
        $this->beginElement('subroutineBody');
        $this->compileTerminalSymbol('{');

        while ($this->tokenizer->isKeyword('var')) {
            $nLocals += $this->compileVarDec();
        }
        
        // Function header
        $this->writer->writeFunction($this->className . '.' . $subroutineName, $nLocals);
        
        // Definir o this
        switch ($this->ctx->parentNode->firstChild->nodeValue) {
            case 'constructor':
                $fieldCount = $this->st->varCount('field') + $this->st->varCount('static');
                // *this = Memory.alloc($fieldCount);
                $this->writer->writePush('constant', $fieldCount);
                $this->writer->writeCall('Memory.alloc', 1);
                $this->writer->writePop('pointer', 0);
                break;
            case 'method':
                // *this = $argument[0];
                $this->writer->writePush('argument', 0);
                $this->writer->writePop('pointer', 0);
                break;
        }
        // end of header
        
        $this->compileStatements();
        $this->compileTerminalSymbol('}');
        $this->endElement();
        return $nLocals;
    }

    // 'var' type varName (',' varName)* ';'
    public function compileVarDec()
    {
        $this->beginElement('varDec');
        $this->compileTerminalKeyword('var');
        $type = $this->compileType();
        $id = $this->identifier();
        $this->st->define($id, $type, 'var');
        $nLocals = 1;
        
        while ($this->tokenizer->isSymbol(',')) {
            $this->compileTerminalSymbol(',');
            $id = $this->identifier();
            $this->st->define($id, $type, 'var');
            $nLocals++;
        }
        
        $this->compileTerminalSymbol(';');
        $this->endElement();
        return $nLocals;
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
        $subroutineName = $this->identifier();
        $this->compileSubroutineCall($subroutineName);
        $this->compileTerminalSymbol(';');
        // descartar resultado da chamada
        $this->writer->writePop('temp', self::TEMP_DO_RETURN);
        $this->endElement();
    }

    // 'let' varName ('[' expression ']')? '=' expression ';'
    public function compileLet()
    {
        $this->beginElement('letStatement');
        $this->compileTerminalKeyword('let');
        $id = $this->identifier();
        $symbol = $this->st->get($id);
        $isArray = false;
        
        if ($this->tokenizer->isSymbol('[')) {
            $isArray = true;
            $this->compileTerminalSymbol('[');
            $this->compileExpression();
            // pop index
            $this->writer->writePop('temp', self::TEMP_ARRAY_INDEX);
            $this->compileTerminalSymbol(']');
        }
        
        $this->compileTerminalSymbol('=');
        $this->compileExpression();
        $this->compileTerminalSymbol(';');
        
        if ($isArray) {
            // push varName
            $this->compileIdentifier($id);
            // push index
            $this->writer->writePush('temp', self::TEMP_ARRAY_INDEX);
            // add varName, index
            $this->writer->writeArithmetic('+');
            // set that
            $this->writer->writePop('pointer', 1);
            // *that = top of the stack
            $this->writer->writePop('that', 0);
        } else {
            // varName = top of the stack
            $this->writer->writePop($symbol->kind, $symbol->index);
        }
        
        $this->endElement();
    }

    public function getNextLabel()
    {
        $next = $this->labelCounter;
        $this->labelCounter++;
        return $next;
    }
    
    // 'while' '(' expression ')' '{' statements '}'
    public function compileWhile()
    {
        $labelCounter = $this->getNextLabel();
        $begin = 'WHILE_BEGIN' . $labelCounter;
        $end = 'WHILE_END' . $labelCounter;
        
        $this->beginElement('whileStatement');
        $this->compileTerminalKeyword('while');
        $this->compileTerminalSymbol('(');
        
        $this->writer->writeLabel($begin);
        $this->compileExpression();
        
        $this->compileTerminalSymbol(')');
        $this->compileTerminalSymbol('{');
        
        // negar condição: !(cond)
        $this->writer->writeUnaryOp('~');
        $this->writer->writeIf($end);
        $this->compileStatements();
        $this->writer->writeGoto($begin);
        
        $this->compileTerminalSymbol('}');
        
        $this->writer->writeLabel($end);
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
        
        $this->writer->writeReturn();
    }

    // 'if' '(' expression ')' '{' statements '}' ('else' '{' statements '}')?
    public function compileIf()
    {
        $labelCounter = $this->getNextLabel();
        $end = 'IF_END' . $labelCounter;
        $else = 'ELSE_BEGIN' . $labelCounter;
        
        $this->beginElement('ifStatement');
        $this->compileTerminalKeyword('if');
        $this->compileTerminalSymbol('(');
        
        $this->compileExpression();
        // negar condição: !(cond)
        $this->writer->writeUnaryOp('~');
        $this->writer->writeIf($else);
        
        $this->compileTerminalSymbol(')');
        $this->compileTerminalSymbol('{');
            
        $this->compileStatements();
        $this->writer->writeGoto($end);
        
        $this->compileTerminalSymbol('}');
        
        $this->writer->writeLabel($else);
        if ($this->tokenizer->isKeyword('else')) {
            $this->compileTerminalKeyword('else');
            $this->compileTerminalSymbol('{');
            $this->compileStatements();
            $this->compileTerminalSymbol('}');
        }
        
        $this->writer->writeLabel($end);
        
        $this->endElement();
    }

    // subroutineName '(' expressionList ')' | 
    //  (className | varName) '.' subroutineName '(' expressionList ')'
    // O primeiro identifier fica por conta do caller
    protected function compileSubroutineCall($identifier)
    {
        $nArgs = 0;
        
        if ($this->tokenizer->isSymbol('.')) {
            // (className | varName)
            if ($this->st->contains($identifier)) {
                // varName -> method call
                $className = $this->st->typeOf($identifier);
                // passar varName como this
                $this->compileIdentifier($identifier);
                $nArgs++;
            } else {
                // className -> function or constructor call
                $className = $identifier;
            }
            $this->compileTerminalSymbol('.');
            $subroutineName = $this->identifier();
        } else {
            // this -> method call
            $className = $this->className;
            $subroutineName = $identifier;
            // repassar o this desse método para o próximo
            $this->writer->writePush('pointer', 0);
            $nArgs++;
        }
        
        $this->compileTerminalSymbol('(');
        $nArgs += $this->compileExpressionList();
        $this->compileTerminalSymbol(')');
        $this->writer->writeCall($className . '.' . $subroutineName, $nArgs);
    }
    
    /** Expressions **/

    // term (op term)*
    public function compileExpression()
    {
        $this->beginElement('expression');
        $this->compileTerm();
        
        $op = array('+', '-', '*', '/', '&', '|', '<', '>', '=');
        
        while ($this->tokenizer->isSymbol($op)) {
            $symbol = $this->tokenizer->symbol();
            $this->compileTerminalSymbol($op);
            $this->compileTerm();
            $this->writer->writeArithmetic($symbol);
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
            $this->writer->writePush('constant', $this->tokenizer->intVal());
            $this->advance();
        } elseif ($this->tokenizer->isString()) {
            $this->compileString($this->tokenizer->stringVal());
            $this->addTerminal($this->tokenizer->stringValToken());
            $this->advance();
        } elseif ($this->tokenizer->isKeyword('true', 'false', 'null', 'this')) {
            switch ($this->tokenizer->keyword()) {
                case 'true': // -1
                    $this->writer->writePush('constant', '1');
                    $this->writer->writeUnaryOp('-');
                    break;
                case 'false':
                case 'null':
                    $this->writer->writePush('constant', '0');
                    break;
                case 'this':
                    $this->writer->writePush('pointer', 0);
                    break;
            }
            $this->compileTerminalKeyword('true', 'false', 'null', 'this');
        } elseif ($this->tokenizer->isIdentifier()) {
            // varName
            $varName = $this->identifier();
            
            // '[' expression ']'
            if ($this->tokenizer->isSymbol('[')) {
                // push varName
                $this->compileIdentifier($varName);
                $this->compileTerminalSymbol('[');
                // push expression
                $this->compileExpression();
                $this->compileTerminalSymbol(']');
                // add varName, expression
                $this->writer->writeArithmetic('+');
                // set that
                $this->writer->writePop('pointer', 1);
                // push *that
                $this->writer->writePush('that', 0);
            } elseif ($this->tokenizer->isSymbol('(', '.')) {
                // subroutineCall
                $this->compileSubroutineCall($varName);
            } else {
                // simple var ref
                $this->compileIdentifier($varName);
            }
        } elseif ($this->tokenizer->isSymbol('(')) {
            $this->compileTerminalSymbol('(');
            $this->compileExpression();
            $this->compileTerminalSymbol(')');
        } elseif ($this->tokenizer->isSymbol('-', '~')) {
            $this->compileTerminalSymbol('-', '~');
            $this->compileTerm();
            
            $node = $this->ctx->firstChild;
            $this->writer->writeUnaryOp($node->nodeValue);
        } else {
            throw new ParserError("Termo inválido");
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
            return 0;
        }
        $this->compileExpression();
        $count = 1;
        
        while ($this->tokenizer->isSymbol(',')) {
            $this->compileTerminalSymbol(',');
            $this->compileExpression();
            $count++;
        }
        
        $this->endElement();
        return $count;
    }
    /** }}} */
    
    protected function compileIdentifier($id)
    {
        $symbol = $this->st->get($id);
        $this->writer->writePush($symbol->kind, $symbol->index);
    }
    
    protected function compileString($str)
    {
        $this->writer->writePush('constant', strlen($str));
        $this->writer->writeCall('String.new', 1);
        foreach (str_split($str) as $char) {
            $this->writer->writePush('constant', ord($char));
            $this->writer->writeCall('String.appendChar', 2);
        }
    }
}

<?php

namespace JackCompiler;

// iterator interfaces
use IteratorAggregate;
use AppendIterator;
use ArrayIterator;
use Exception;

class SymbolTable implements IteratorAggregate
{
    protected $classScope;
    protected $subroutineScope;

    /**
     * Creates a new copy of the symbol table
     */
    public function __construct()
    {
        $this->classScope = array();
        $this->subroutineScope = array();
    }

    public function getIterator()
    {
        $it = new AppendIterator();
        $it->append(new ArrayIterator($this->subroutineScope));
        $it->append(new ArrayIterator($this->classScope));
        return $it;
    }
    
    public function get($identifier)
    {
        if (array_key_exists($identifier, $this->subroutineScope)) {
            return $this->subroutineScope[$identifier];
        } elseif (array_key_exists($identifier, $this->classScope)) {
            return $this->classScope[$identifier];
        } else {
            throw new Exception("Identificador não encontrado: " . $identifier);
        }
    }

    public function contains($identifier)
    {
        if (array_key_exists($identifier, $this->subroutineScope) ||
            array_key_exists($identifier, $this->classScope)) {
            return true;
        }
        return false;
    }

    // ** Book API ** //

    /**
     * Starts a new subroutine scope (i.e., resets the subroutine's symbol table)
     *
     * @return void
     */
    public function startSubroutine()
    {
        $this->subroutineScope = array();
    }

    /**
     * Defines a new identifier of a given name, type and kind and assigns it
     * a running index. STATIC and FIELD identifiers have a class scope,
     * while ARG and VAR identifiers have a subroutine scope.
     *
     * @param String $name
     * @param String $type
     * @param enum $kind (STATIC, FIELD, ARG, VAR)
     * @return void
     */
    public function define($name, $type, $kind)
    {
        if ($this->contains($name)) {
            throw new Exception('símbolo "' . $name . '" duplicado');
        }
        
        $symbol = new Symbol($name, $type, $kind, $this->varCount($kind));
        
        if (in_array($kind, array('arg', 'var'))) {
            $this->subroutineScope[$name] = $symbol;
        } elseif (in_array($kind, array('static', 'field'))) {
            $this->classScope[$name] = $symbol;
        } else {
            throw new Exception("Categoria ($kind) inválida do identificador $name");
        }
    }

    /**
     * Returns the number of variables of the given kind already defined in the
     * current scope.
     *
     * @param enum $kind (STATIC, FIELD, ARG, VAR)
     * @return int
     */
    public function varCount($kind)
    {
        $count = 0;
        foreach ($this as $id => $symbol) {
            if ($symbol->kind == $kind) {
                $count++;
            }
        }
        return $count;
    }

    /**
     * Returns the kind of the named identifier in the current scope. If the
     * identifier is unknow in the current scope, returns NONE.
     *
     * @param String $name
     * @return enum (STATIC, FIELD, ARG, VAR)
     */
    public function kindOf($name)
    {
        return $this->get($name)->kind;
    }

    /**
     * Returns the type of the named identifier in the current scope.
     *
     * @param String $name
     * @return String
     */
    public function typeOf($name)
    {
        return $this->get($name)->type;
    }

    /**
     * Returns the index assigned to the named identifier.
     * @param String $name
     * @return int
     */
    public function indexOf($name)
    {
        return $this->get($name)->index;
    }
}

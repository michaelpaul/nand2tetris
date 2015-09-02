<?php

namespace VMTranslator;

class ParserError extends \Exception
{
}

/**
 * Class Parser
 */
class Parser
{
    const C_ARITHMETIC = 1;
    const C_PUSH = 2;
    const C_POP = 3;
    const C_LABEL = 4;
    const C_GOTO = 5;
    const C_IF = 6;
    const C_FUNCTION = 7;
    const C_RETURN = 8;
    const C_CALL = 9;

    private $fp;

    private $cmd;
    private $cmd_type;
    private $arg1;
    private $arg2;

    public function __construct($inputFile = null)
    {
        if (!file_exists($inputFile)) {
            die("arquivo não econtrado: " . $inputFile . "\n");
        }
        $this->fp = fopen($inputFile, 'r');
    }

    public function hasMoreCommands()
    {
        // ignore comments and empty lines
        do {
            $cmd = trim(fgets($this->fp));
        } while (!feof($this->fp) && (empty($cmd) || substr($cmd, 0, 2) == '//'));

        if (empty($cmd)) {
            return false;
        }
        $this->cmd = $cmd;
        return true;
    }

    public function advance()
    {
        $this->cmd_type = null;
        $this->arg1 = null;
        $this->arg2 = null;

        $tokens = explode(' ', $this->cmd);
        $cmd = array();

        foreach ($tokens as $key => $v) {
            if ($v == '//') {
                break;
            }
            // skip whitespace
            if (ctype_space($v) || $v == '') {
                continue;
            }
            $cmd[] = strtolower($v);
        }

        switch (count($cmd)) {
            case 3:
                $this->cmd_type = $cmd[0];
                $this->arg1 = $cmd[1];
                $this->arg2 = $cmd[2];
                break;
            case 2:
                $this->cmd_type = $cmd[0];
                $this->arg1 = $cmd[1];
                break;
            case 1:
                $this->cmd_type = $cmd[0];
                $this->arg1 = $cmd[0];
                break;
            default:
                throw new ParserError("Erro de syntaxe");
        }
    }

    /**
     * @return int
     */
    public function commandType()
    {
        $types = array(
            'add' => Parser::C_ARITHMETIC,
            'sub' => Parser::C_ARITHMETIC,
            'neg' => Parser::C_ARITHMETIC,
            'eq' => Parser::C_ARITHMETIC,
            'lt' => Parser::C_ARITHMETIC,
            'gt' => Parser::C_ARITHMETIC,
            'not' => Parser::C_ARITHMETIC,
            'and' => Parser::C_ARITHMETIC,
            'or' => Parser::C_ARITHMETIC,
            'push' => Parser::C_PUSH,
            'pop' => Parser::C_POP,
            'label' => Parser::C_LABEL,
            'if-goto' => Parser::C_IF,
            'goto' => Parser::C_GOTO,
            'function' => Parser::C_FUNCTION,
            'return' => Parser::C_RETURN,
        );

        if (array_key_exists($this->cmd_type, $types)) {
            return $types[$this->cmd_type];
        } else {
            throw new ParserError("Tipo do comando desconhecido: " . $this->cmd_type);
        }
    }

    public function commandTypeLabel()
    {
        return $this->cmd_type;
    }

    /**
     * @return string
     */
    public function arg1()
    {
        return $this->arg1;
    }

    /**
     * @return string
     */
    public function arg2()
    {
        return $this->arg2;
    }

    public function __destruct()
    {
        if ($this->fp) {
            fclose($this->fp);
        }
    }
}

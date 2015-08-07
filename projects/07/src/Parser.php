<?php

namespace VMTranslator;

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

        foreach ($tokens as $key => $value) {
            $v = strtolower(trim($value));
            if ($v == '//') {
                break;
            }
            $cmd[] = $v;
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
            case 1:
                $this->cmd_type = $cmd[0];
                $this->arg1 = $cmd[0];
            default:
                return;
        }
    }

    /**
     * @return int
     */
    public function commandType()
    {
        $types = array(
            'add' => Parser::C_ARITHMETIC,
            'push' => Parser::C_PUSH,
            'pop' => Parser::C_POP,
        );

        if (array_key_exists($this->cmd_type, $types)) {
            return $types[$this->cmd_type];
        }
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

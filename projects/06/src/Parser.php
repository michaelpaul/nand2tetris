<?php

namespace Assembler;

/**
 * Class Parser
 */
class Parser
{
    const A_COMMAND = 1;
    const C_COMMAND = 2;
    const L_COMMAND = 3;

    private $fp;

    private $cmd_type;
    private $symbol;
    private $dest;
    private $comp;
    private $jump;

    public function __construct($inputFile = null)
    {
        if (!file_exists($inputFile)) {
            die("arquivo não econtrado: " . $inputFile . "\n");
        }
        $this->fp = fopen($inputFile, 'r');
    }

    public function hasMoreCommands()
    {
        return !feof($this->fp);
    }

    // dest=comp;jump
    // dest=comp
    // comp;jump

    // either the dest or jump fields may be empty.
    // if dest is empty, the "=" is omitted;
    // if jump is empty, the ";" is omitted.

    public function advance()
    {
        $buffer = '';
        $this->cmd_type = null;
        $this->symbol = null;
        $this->comp = null;
        $this->dest = null;
        $this->jump = null;

        // ignore comments and empty lines
        do {
            $command = trim(fgets($this->fp));
        } while ($this->hasMoreCommands() && (empty($command) || substr($command, 0, 2) == '//'));

        if (empty($command)) {
            return;
        }

        // parse type and symbol/label
        if ($command[0] == '@') {
            $this->cmd_type = self::A_COMMAND;
            $this->symbol = substr($command, 1, strlen($command));
            return;
        } else if ($command[0] == '(' && substr($command, -1, 1) == ')') {
            $this->cmd_type = self::L_COMMAND;
            $this->symbol = substr($command, 1, strlen($command) - 2);
            return;
        }

        // parse c command
        $this->cmd_type = self::C_COMMAND;
        $chars = str_split($command);
        foreach ($chars as $c) {
            // ignore whitespace
            if (ctype_space($c)) {
                continue;
            }
            // tokenize
            if ($c == '=') {
                $this->dest = $buffer;
                $buffer = '';
                continue;
            } else if ($c == ';') {
                $this->comp = $buffer;
                $buffer = '';
                continue;
            }
            $buffer .= $c;
        }
        // get last token
        if (is_null($this->comp)) {
            $this->comp = $buffer;
        } else if (is_null($this->jump)) {
            $this->jump = $buffer;
        }
    }

    public function commandType()
    {
        return $this->cmd_type;
    }

    public function symbol()
    {
        return $this->symbol;
    }

    public function dest()
    {
        return $this->dest;
    }

    public function comp()
    {
        return $this->comp;
    }

    public function jump()
    {
        return $this->jump;
    }

    public function __destruct()
    {
        if ($this->fp) {
            fclose($this->fp);
        }
    }
}

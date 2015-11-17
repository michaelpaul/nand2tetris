<?php

/**
 * This is project's console commands configuration for Robo task runner.
 *
 * @see http://robo.li/
 */
class RoboFile extends \Robo\Tasks
{
    // define public methods as commands

    public function tests()
    {
        $path = "tests/programs/Square";
        $files = array('Main', 'Square', 'SquareGame');
        foreach ($files as $file) {
            $output = "/tmp/{$file}T.xml";
            $this->_exec("php JackTokenizer.php {$path}/{$file}.jack $output");
            $this->_exec("TextComparer.sh {$path}/{$file}T.xml $output");
        }
    }
}

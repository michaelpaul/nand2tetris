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
        $this->stopOnFail(true);
        $this->taskPHPUnit('phpunit')
            ->run();

        $path = 'tests/programs';
        $files = array(
            'Square/Main', 
            'Square/SquareGame',
            'Square/Square', 
            'ArrayTest/Main',
        );
        foreach ($files as $file) {
            $output = '/tmp/' . str_replace('/', '.', $file) . 'T.xml';
            $this->_exec("php JackTokenizer.php {$path}/{$file}.jack $output");
            $this->_exec("TextComparer.sh {$path}/{$file}T.xml $output");
        }
    }
}

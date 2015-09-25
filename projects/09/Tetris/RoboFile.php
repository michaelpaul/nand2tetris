<?php

/**
 * This is project's console commands configuration for Robo task runner.
 *
 * @see http://robo.li/
 */
class RoboFile extends \Robo\Tasks
{
    // define public methods as commands

    public function build()
    {
        $this->_exec('JackCompiler.sh');
    }

    public function watch()
    {
        $files = glob(__DIR__ . '/*.jack');
        $this->taskWatch()->monitor($files, function() {
            $this->build();
        })->run();
    }

    public function clean()
    {
        $this->_exec('rm *.vm');
    }
}

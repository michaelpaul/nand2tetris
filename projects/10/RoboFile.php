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

        $this->tokenizerTest();
        $this->parserTest();
    }
    
    protected function tokenizerTest()
    {
        $path = 'tests/programs';
        $files = array(
            'ExpressionlessSquare/Main', 
            'ExpressionlessSquare/SquareGame',
            'ExpressionlessSquare/Square',
            'Square/Main', 
            'Square/SquareGame',
            'Square/Square', 
            'ArrayTest/Main',
        );
        foreach ($files as $file) {
            $output = '/tmp/' . str_replace('/', '.', $file) . 'T.xml';
            $this->_exec("./bin/tokenizer.php {$path}/{$file}.jack $output");
            $this->_exec("TextComparer.sh {$path}/{$file}T.xml $output");
        }
    }
    
    protected function parserTest()
    {
        $path = 'tests/programs';
        $files = array(
            'ExpressionlessSquare/Main', 
            'ExpressionlessSquare/SquareGame',
            'ExpressionlessSquare/Square', 
        );
        foreach ($files as $file) {
            $output = '/tmp/' . str_replace('/', '.', $file) . '.xml';
            $this->_exec("./bin/parser.php {$path}/{$file}.jack $output");
            $this->_exec("TextComparer.sh {$path}/{$file}.xml $output");
        }
    }
}

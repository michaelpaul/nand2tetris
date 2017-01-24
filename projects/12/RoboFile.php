<?php
/**
 * This is project's console commands configuration for Robo task runner.
 *
 * @see http://robo.li/
 */
class RoboFile extends \Robo\Tasks
{
    public function clean()
    {
        $this->_cleanDir('./build');
    }

    /**
    * Compila o OS e instala ele nos programas de teste
    *
    * @param array $opts
    * @option $jsh Usar o compilador oficial (JackCompiler.sh)
    */
    public function build($opts = ['jsh' => false])
    {
        // meu compilador
        $jackCompiler = '../10-11/bin/JackCompiler.php ';
        
        if ($opts['jsh']) {
            $jackCompiler = 'JackCompiler.sh ';
        } 

        $this->_mkdir('build');
        $osClass = [
            'Array', 'Keyboard', 'Math', 'Memory', 'Output', 'Screen', 'String',  'Sys'
        ];
        foreach ($osClass as $key => $klass) {
            $dir = $klass . "Test/";
            $this->_exec($jackCompiler . $dir);
            $this->_copy($dir . $klass . ".vm", "./build/{$klass}.vm");
        }

        // compilar os programas de teste e instalar o OS
        $tetris = '../09/Tetris';
        $this->_copyDir('./build', $tetris);
        $this->_exec($jackCompiler . $tetris);
        
        $pong = '../10-11/tests/programs/11/Pong';
        $this->_copyDir('./build', $pong);
        $this->_exec($jackCompiler . $pong);
    }
}
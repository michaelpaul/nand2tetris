<?php

namespace JackTests;

class StatementsTests extends CompilerTestCase
{
    public function testLet()
    {
        $this->writeTestProgram('let game = x;');
        $this->parser->advance();
        $this->parser->compileLet();

        $expected = '
            <letStatement>
                <keyword>let</keyword>
                <identifier>game</identifier>
                <symbol>=</symbol>
                <expression>
                  <term>
                    <identifier>x</identifier>
                  </term>
                </expression>
                <symbol>;</symbol>
            </letStatement>
        ';

        $this->assertASTEquals($expected);
    }
    
    public function testLetArray()
    {
        $this->writeTestProgram('let vector[x] = sector;');
        $this->parser->advance();
        $this->parser->compileLet();

        $expected = '
            <letStatement>
                <keyword>let</keyword>
                <identifier>vector</identifier>
                <symbol>[</symbol>
                <expression>
                  <term>
                    <identifier>x</identifier>
                  </term>
                </expression>
                <symbol>]</symbol>
                <symbol>=</symbol>
                <expression>
                  <term>
                    <identifier>sector</identifier>
                  </term>
                </expression>
                <symbol>;</symbol>
            </letStatement>
        ';

        $this->assertASTEquals($expected);
    }
}
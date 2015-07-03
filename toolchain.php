<?php

use \MyCompiler\JackCompiler;
use \MyVMTranslator\VMTranslator;
use \MyAssembler\Assembler;

/**
 * 1 - Hack: hardware and software stack
 * From Jack (program, app, OS) to a Hack executable.
 */

$cc = new JackCompiler();
$vmt = new VMTranslator();
$as = new Assembler();

$jack_file = 'Sum.jack';

// Using MY full toolchain
// Jack -> VM -> ASM -> Hack

// system('JackCompiler.sh ' . $jack_file);
$vm_file = $cc->compile($jack_file);
// VM -> ASM: Apenas com meu VMTranslator
$asm_file = $vmt->translate($vm_file);
// system('Assembler.sh ' . $asm_file);
$hack_file = $as->assemble($asm_file);


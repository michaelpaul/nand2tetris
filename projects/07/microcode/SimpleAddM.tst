load SimpleAddM.asm,
output-file SimpleAddM.out,
compare-to SimpleAddM.cmp,
output-list RAM[0]%D2.6.2 RAM[256]%D2.6.2;

set RAM[0] 256,

repeat 60 {
  ticktock;
}

output;

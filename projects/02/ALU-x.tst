load ALU-dev.hdl,
output-file ALU-x.out,
// compare-to ALU-x.cmp,
output-list wdx%B1.16.1 out%B1.16.1
            zx%B1.1.1 nx%B1.1.1
            x%B1.16.1 y%B1.16.1;

set x 155,
set y 23;

// 00
set zx 0,
set nx 0,
eval,
output;

// 01
set zx 0,
set nx 1,
eval,
output;
// 10
set zx 1,
set nx 0,
eval,
output;
// 11
set zx 1,
set nx 1,
eval,
output;


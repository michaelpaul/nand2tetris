load ALU.hdl,
output-file ALU-dev.out,
output-list wzx%B1.16.1 wnx%B1.16.1 wx%B1.16.1
            zx%B1.1.1 nx%B1.1.1
            x%B1.16.1 y%B1.16.1  zy%B1.1.1
            ny%B1.1.1 f%B1.1.1 no%B1.1.1 out%B1.16.1 zr%B1.1.1
            ng%B1.1.1;

set x 155,
set y 23;

// zera x
set zx 1, set nx 0, set zy 0, set ny 0, set f  0, set no 0,
eval,
output;
// N zera x
set zx 0, set nx 0, set zy 0, set ny 0, set f  0, set no 0,
eval,
output;

// zera x e nx
set zx 1, set nx 1, set zy 0, set ny 0, set f  0, set no 0,
eval,
output;
// N zera x e nx
set zx 0, set nx 0, set zy 0, set ny 0, set f  0, set no 0,
eval,
output;


// Multiplies R0 and R1 and stores the result in R2.
// (R0, R1, R2 refer to RAM[0], RAM[1], and RAM[2], respectively.)

// @2
// D=A
// @R0
// M=D

// @6
// D=A
// @R1
// M=D

@R2 // b, sum
M=0

@i
M=0

(LOOP)
    // if not condition, leave loop
    @R1
    D=M
    @i
    D=D-M
    @DONE
    D;JEQ

    // loop code
    @R0
    D=M
    @R2
    M=M+D
    @i
    M=M+1
    // loop code

    @LOOP
    0;JMP

(DONE)

(END)
    @END
    0;JMP


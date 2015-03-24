// Runs an infinite loop that listens to the keyboard input.
// When a key is pressed (any key), the program blackens the screen,
// i.e. writes "black" in every pixel. When no key is pressed, the
// program clears the screen, i.e. writes "white" in every pixel.

// Device addresses: @SCREEN, @KBD

(GETKEY)
    @color
    M=0

    @KBD
    D=M
    // if not keypress
    @GOTKEY
    D;JEQ
    @color
    M=-1

(GOTKEY)
    // init screenptr
    @SCREEN
    D=A
    @screenptr
    M=D

(LOOP)
    // paint screen
    // while screenptr < KBD; enter loop
    // if screenptr - KBD == 0; leave loop
    // if not condition; leave loop
    @KBD
    D=A
    @screenptr
    D=D-M
    @PAINT_DONE
    D;JEQ

    @color
    D=M
    @screenptr
    A=M
    M=D // m[a] = color

    @screenptr
    M=M+1
    @LOOP
    0;JMP

(PAINT_DONE)
    @GETKEY
    0;JMP


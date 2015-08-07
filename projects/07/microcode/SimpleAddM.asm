// push const 7
    @7
    D=A
    @SP
    A=M
    M=D
    // sp++
    @SP
    M=M+1

// push const 8
    @8
    D=A
    @SP
    A=M
    M=D
    // sp++
    @SP
    M=M+1

// add
    // pop R5
    @SP
    A=M-1
    D=M

    @R5
    M=D

    @SP
    M=M-1

    // pop R6
    @SP
    A=M-1
    D=M

    @R6
    M=D

    @SP
    M=M-1

    // D = R5+R6
    @R5
    D=M
    @R6
    D=D+M

    // push D
    @SP
    A=M
    M=D
    // sp++
    @SP
    M=M+1 

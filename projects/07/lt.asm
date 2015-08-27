    @x
    M=-1
    @y
    M=1

    @x
    D=M
    @y
    D=D-M // x - y
    @FALSE
    D;JGE // if ($x - $y > 0) goto f;
    @r
    M=-1 // $r = true;
    @END
    0;JMP // goto end;
(FALSE) // f:
    @r
    M=0 // $r = false;
(END) // end:
    @r
    D=M // return $r;

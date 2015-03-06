SOURCE=$(abspath Mult.asm)
BIN=$(abspath Mult.hack)
TEST_SCRIPT=$(abspath Mult.tst)

ASSEMBLER=Assembler.sh
EMULATOR=CPUEmulator.sh

all: $(BIN)

$(BIN): $(SOURCE)
	$(ASSEMBLER) $(SOURCE)

test: $(BIN)
	$(EMULATOR) $(TEST_SCRIPT)

clean:
	rm -f $(BIN) *.out

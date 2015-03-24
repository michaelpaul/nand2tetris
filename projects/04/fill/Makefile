SOURCE=$(abspath Fill.asm)
BIN=$(abspath Fill.hack)
TEST_SCRIPT=$(abspath Fill.tst)

ASSEMBLER=Assembler.sh
EMULATOR=CPUEmulator.sh

all: $(BIN)

$(BIN): $(SOURCE)
	$(ASSEMBLER) $(SOURCE)

test: $(BIN)
	$(EMULATOR) $(TEST_SCRIPT)

clean:
	rm -f $(BIN) *.out

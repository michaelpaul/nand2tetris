.PHONY: unit test clean

SOURCE=tests/sample.vm
ASSEMBLY=tests/functional/sample.asm
TEST_SCRIPT=tests/functional/sample.tst

EMULATOR=CPUEmulator.sh

all:

$(ASSEMBLY): $(SOURCE) src/*.php
	php VMTranslator.php $(SOURCE) > $(ASSEMBLY)

unit:
	phpunit

test: unit $(ASSEMBLY)
	$(EMULATOR) $(TEST_SCRIPT)

clean:
	rm -f $(ASSEMBLY) tests/functional/*.out

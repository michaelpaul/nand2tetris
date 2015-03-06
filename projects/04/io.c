#include <stdio.h>

int RAM[0x6000]; // 24576

/* screen size: (256 * 512) / 16 */
// ie. 8K memory addresses/map
int screen   = 0x4000;
int keyboard = 0x6000;

void printScreen() {
    int lastPage = keyboard;

    for (int page = screen; page < lastPage; page++) {
        printf("%d ", RAM[page]);
    }
}

void paintScreen(int color) {
    int lastPage = keyboard;
    printf("color: %d\n", color);

    for (int page = screen; page < lastPage; page++) {
        RAM[page] = color;
    }
}

int main() {
    /* while (1) { */
        int color;
        RAM[keyboard] = 0;

        // no keypress, deixar tela branca
        color = 0;

        if (RAM[keyboard] != 0) {
            // keypressed, deixar tela preta
            color = -1;
        }

        paintScreen(color);
        printScreen();
    /* } */
}

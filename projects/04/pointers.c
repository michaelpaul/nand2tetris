#include <stdio.h>

// Ponteiros no C

int main() {
    int a = 512;

    int * x = &a;
    int * y;

    y = &a;

    printf("X address: %p, value: %d \n", x, *x);
    printf("Y address: %p, value: %d", y, *y);

    return 0;
}

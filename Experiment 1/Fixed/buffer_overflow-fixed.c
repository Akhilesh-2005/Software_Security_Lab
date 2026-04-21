#include <stdio.h>

void greet() {
    char buffer[8];

    printf("Enter your name: ");
    scanf("%7s", buffer);   

    printf("Hello %s\n", buffer);
}

int main() {
    greet();
    return 0;
}
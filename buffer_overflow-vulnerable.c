#include <stdio.h>
#include <string.h>

void greet() {
    char buffer[8];

    printf("Enter your name: ");
    scanf("%s", buffer);   
    printf("Hello %s\n", buffer);
}

int main() {
    greet();
    return 0;
}
#include <stdio.h>

int main(void) {
  int c;
  int lineNo = 1;
  int charNo = 0;
  while ((c = getchar()) != EOF) {
    if (c == '\n') {
      lineNo++;
      charNo = 1;
    } else {
      charNo++;
      if (c < 32 || c > 127) {
	printf("chr(%d) at line %d, char %d\n", c, lineNo, charNo);
      }
    }
  }
  return 0;
}

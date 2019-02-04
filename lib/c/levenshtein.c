/**
 * Computes the Levenshtein distance from one query string to a sorted list of
 * dictionary strings. Prints those having a Levenshtein distance of at most
 * maxDistance.
 *
 * Letters on neighboring keys and pairs of symbols with diacritics count less
 * than the distance between random pairs of symbols.
 *
 * Each word in the dictionary is given as <p><s>, where
 * - p is the length of the common prefix between the this word and the previous one;
 * - s is the suffix obtained by removing the first p characters from the word.
 *
 * This allows Levenshtein to skip the first p rows of the distance matrix,
 * which must already be computed. In practice, this cuts the number of
 * computations to about a third. Additionally, if at any step the row minimum
 * exceeds maxDistance, we bail out because we can never get back below
 * maxDistance. (This is technically not true because of transpositions, but
 * it doesn't matter much in practice.)
 **/
#include <errno.h>
#include <locale.h>
#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include <wchar.h>

#define COST_INS 10
#define COST_DEL 10
#define COST_TRANSPOSE 5
#define COST_KBD 8
#define COST_DIACRITICS 2
#define COST_OTHER 15
#define INFTY 10000
#define MAX_LENGTH 100

#define DIA_A_HAT 0x103
#define DIA_A_CIRC 0xe2
#define DIA_I_CIRC 0xee
#define DIA_S_COMMA 0x219
#define DIA_T_COMMA 0x21b

/* pairs of neighboring letter keys */
#define NUM_KEY_PAIRS 55
char* KEY_PAIRS[NUM_KEY_PAIRS] =
  { "qw", "we", "er", "rt", "ty", "yu", "ui", "io", "op",
    "as", "sd", "df", "fg", "gh", "hj", "jk", "kl",
    "zx", "xc", "cv", "vb", "bn", "nm",
    "qa", "ws", "ed", "rf", "tg", "yh", "uj", "ik", "ol",
    "wa", "es", "rd", "tf", "yg", "uh", "ij", "ok", "pl",
    "az", "sx", "dc", "fv", "gb", "hn", "jm",
    "sz", "dx", "fc", "gv", "hb", "jn", "km" };

/* special distances for frequent symbols (less than COST_OTHER) */
#define ALPHABET_SIZE 31

#define MIN(X, Y) (((X) < (Y)) ? (X) : (Y))

short symbolDist[ALPHABET_SIZE + 1][ALPHABET_SIZE + 1];
short mat[MAX_LENGTH][MAX_LENGTH];
wchar_t chars[MAX_LENGTH], queryChars[MAX_LENGTH], orig[MAX_LENGTH];
int clen, qclen; /* lengths of chars and queryChars */
int prevEndRow = 0; /* first row not scanned on previous run */
int maxDistance;

void die(char* msg) {
  fprintf(stderr, "%s\n", msg);
  exit(1);
}

int translate(int c) {
  if (c >= 'a' && c <= 'z') {
    return c - 'a';
  }

  switch (c) {
    case DIA_A_HAT: return 26;
    case DIA_A_CIRC: return 27;
    case DIA_I_CIRC: return 28;
    case DIA_S_COMMA: return 29;
    case DIA_T_COMMA: return 30;
  }

  return ALPHABET_SIZE;
}

void initMatrix() {
  for (int i = 0; i < MAX_LENGTH; i++) {
    mat[i][0] = i * COST_INS;
    mat[0][i] = i * COST_DEL;
  }
}

void setSymbolDist(int c1, int c2, int cost) {
  c1 = translate(c1);
  c2 = translate(c2);
  symbolDist[c1][c2] = symbolDist[c2][c1] = cost;
}

void initDist() {
  /* base case */
  for (int i = 0; i <= ALPHABET_SIZE; i++) {
    for (int j = 0; j <= ALPHABET_SIZE; j++) {
      symbolDist[i][j] = COST_OTHER;
    }
  }

  /* pairs of neighboring letter keys */
  for (int i = 0; i < NUM_KEY_PAIRS; i++) {
    setSymbolDist(KEY_PAIRS[i][0], KEY_PAIRS[i][1], COST_KBD);
  }

  /* diacritics */
  setSymbolDist('a', DIA_A_HAT, COST_DIACRITICS);
  setSymbolDist('a', DIA_A_CIRC, COST_DIACRITICS);
  setSymbolDist('i', DIA_I_CIRC, COST_DIACRITICS);
  setSymbolDist('s', DIA_S_COMMA, COST_DIACRITICS);
  setSymbolDist('t', DIA_T_COMMA, COST_DIACRITICS);
}

/* Core Levenshtein algorithm. Assumes the first startRow rows are already filled. */
/* Values are accurate up to and including maxDistance. */
int dist(int startRow) {
  int d = 0, i;
  int minRowDist = 0; /* minimum distance on previous row */

  for (i = MIN(startRow, prevEndRow);
       (i < clen) && (minRowDist <= maxDistance);
       i++) {
    int x = chars[i];
    minRowDist = INFTY;

    for (int j = 0; j < qclen; j++) {
      int y = queryChars[j];

      /* matrix coordinates are shifted by 1 (so row mat[1] corresponds to chars[0]) */
      if (x == y) {
        d = mat[i][j];
      } else {
        /* transpose last two chars */
        if (i && j &&
            (x == queryChars[j - 1]) &&
            (y == chars[i - 1])) {
          d = mat[i - 1][j - 1] + COST_TRANSPOSE;
        } else {
          d = INFTY;
        }

        /* delete, insert, modify */
        d = MIN(d, mat[i + 1][j] + COST_DEL);
        d = MIN(d, mat[i][j + 1] + COST_INS);
        d = MIN(d, mat[i][j] + symbolDist[x][y]);
      }

      mat[i + 1][j + 1] = d;
      minRowDist = MIN(minRowDist, d);
    }
  }

  prevEndRow = i;
  return d; /* last value computed */
}

int main(int argc, char** argv) {
  if (argc != 4) {
    die("Usage: levenshtein <query> <max_distance> <pattern_file_name>");
  }

  setlocale(LC_ALL, "ro_RO.utf8");
  initMatrix();
  initDist();

  char* query = argv[1];
  maxDistance = atoi(argv[2]);
  char* filename = argv[3];

  /* split the query into wide chars and translate them */
  qclen = mbstowcs(queryChars, query, MAX_LENGTH);
  for (int i = 0; i < qclen; i++) {
    queryChars[i] = translate(queryChars[i]);
  }

  FILE *f = fopen(filename, "r");
  if (!f) {
    die("File not found.");
  }

  int common;
  wint_t c;

  while ((common = fgetwc(f)) != WEOF) {
    /* the length of the common prefix (single digit) */
    common -= '0';

    /* read the suffix, keep a copy of the original and translate it */
    clen = common;
    while ((c = fgetwc(f)) != '\n') {
      orig[clen] = c;
      chars[clen++] = translate(c);
    }
    orig[clen] = '\0';

    int d = dist(common);
    if (d <= maxDistance) {
      printf("%d %S\n", d, orig);
    }
  }

  fclose(f);

  return 0;
}

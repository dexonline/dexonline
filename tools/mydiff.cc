/**
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of the
 * License, or (at your option) any later version.

 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */

/**
 * Reads two strings from stdin, each on a separate line.
 * Prints the diff of the two strings, all on one line, in the format:
 * (<start1>,<length1>)string1(<start2>,<length2>)string2...
 *
 * This indicates that the portion from start1 of length length1 in
 * the first string (which is equal to string1) was replaced with the
 * portion from start2 of length length2 in the second string (which
 * is equal to string2). All the indices are 0-based
 * 
 * Example: s1 = abcabba, s2 = cbabac
 * Output: (0,2)ab(0,0)(3,0)(1,1)b(5,1)b(4,0)(7,0)(5,1)c
 * This means that, in order to transform s1 in s2, ab was deleted, b
 * was inserted at position 3, b was deleted at position 5, c was
 * inserted at position 7.
 *
 * Performance: 4 seconds for a 15K-long string with one modification at each
 * end. This allocated about 16K nodes.
 *
 * TODO: Delete unused candidates if memory consumption becomes a problem.
 */

#include <limits.h>
#include <stdio.h>
#include <stdlib.h>
#include <string.h>

#define MAX_LENGTH 100000

typedef struct {
  int x;
  int y;
} pair;

typedef struct _list {
  int x;
  int y;
  int ref_count;
  struct _list* next;
  struct _list* parent;
} list;

list* new_list(int x, int y, list* parent) {
  list* l = new list;
  l->x = x;
  l->y = y;
  l->parent = parent;
  l->next = NULL;
  l->ref_count = 0;
  return l;
}

void delete_list(list* l) {
  delete l;
}

// Pair comparator that compares by the first element, then by the second.
int compare_first_second(const void* p1, const void* p2) {
  pair* pair1 = (pair*) p1;
  pair* pair2 = (pair*) p2;
  if (pair1->x == pair2->x)
    return pair1->y - pair2->y;
  else
    return pair1->x - pair2->x;
}

int my_fgets(char* s, int max_len) {
  fgets(s, max_len, stdin);
  int len = strlen(s);
  if (len && s[len - 1] == '\n')
    s[--len] = '\0';
  return len;
}

void find_lcs(char* s1, char* s2, pair* lcs, int* len_lcs) {
  int len1 = strlen(s1);
  int len2 = strlen(s2);

  // sorted2 holds the characters in s2 and their positions, sorted by
  // character.
  pair sorted2[len2];
  for (int j = 0; j < len2; j++) {
    sorted2[j].x = s2[j];
    sorted2[j].y = j;
  }
  qsort(sorted2, len2, sizeof(pair), compare_first_second);

  // sets2 holds, for every char, its [first, last] occurrence in sorted2
  pair sets2[CHAR_MAX + 1];
  memset(sets2, 0, sizeof(sets2));
  sets2[sorted2[0].x].x = 0;
  for (int j = 1; j < len2; j++)
    if (sorted2[j].x != sorted2[j-1].x) {
      sets2[sorted2[j-1].x].y = j;
      sets2[sorted2[j].x].x = j;
    }
  sets2[sorted2[len2-1].x].y = len2;

  // chain[k] holds lists of k-candidates
  list* chain[len1 + 1];
  memset(chain, 0, sizeof(chain));
  chain[0] = new_list(-1, -1, NULL);

  // new elements to be added to the chains at each i. We need to generate
  // them all first, THEN add them to the chain.
  list* new_heads[len1 + 1];
  int levels[len1 + 1];
  int num_new_heads;
  int max_len = 0;

  // Now compare pairs of chars
  for (int i = 0; i < len1; i++) {
    // Quickly find all the j's where s2[j] = s1[i]
    int sorted2_start = sets2[(int)s1[i]].x;
    int sorted2_end = sets2[(int)s1[i]].y;

    int k = 0; // Index in the chains, while merging.
    num_new_heads = 0;

    for (int iter = sorted2_start; iter < sorted2_end; iter++) {
      int j = sorted2[iter].y;
      // Now s1[i] == s2[j]
      // Advance k until (i,j) no longer dominates chain[k]
      while (chain[k + 1] && chain[k + 1]->y <= j) k++;
      if (chain[k]->y < j) {
	if (num_new_heads == 0 || levels[num_new_heads - 1] != k + 1) {
	  new_heads[num_new_heads] = new_list(i, j, chain[k]);
	  new_heads[num_new_heads]->next = chain[k + 1];
	  levels[num_new_heads] = k + 1;
	  chain[k]->ref_count++;
	  num_new_heads++;
	} else {
	  // We already have a new head for chain[k + 1], we'll keep it
	  // because it has a lower y
	}
      } else {
	// j does not dominate chain[k]->y and we just throw this j away.
      }
    }

    // Now add all the new heads to the chains
    for (int h = 0; h < num_new_heads; h++) {
      chain[levels[h]] = new_heads[h];
      if (levels[h] > max_len)
	max_len = levels[h];
    }

//     printf("Step %d, chains are:\n", i);
//     for (int level = 0; level <= max_len; level++) {
//       list* l = chain[level];
//       while (l) {
// 	printf("(%d, %d, %d) ", l->x, l->y, l->ref_count);
// 	l = l->next;
//       }
//       printf("\n");
//     }
//     printf("Max len: %d\n", max_len);
  }

  *len_lcs = max_len;
  list* l = chain[max_len];
  lcs[0].x = -1; lcs[0].y = -1;
  lcs[max_len + 1].x = len1; lcs[max_len + 1].y = len2;
  for (int i = max_len; i; i--, l = l->parent) {
    lcs[i].x = l->x;
    lcs[i].y = l->y;
  }
}

int main(void) {
  char s1[MAX_LENGTH], s2[MAX_LENGTH];
  int len1 = my_fgets(s1, sizeof(s1));
  int len2 = my_fgets(s2, sizeof(s2));

  // Remove the longest common prefix and suffix
  int pref = 0;
  while (s1[pref] && s2[pref] && s1[pref] == s2[pref]) pref++;
  if (s1[pref] != s2[pref]) {
    int j1 = len1 - 1, j2 = len2 - 1;
    while (j1 > pref && j2 > pref && s1[j1] == s2[j2]) {
      j1--; j2--;
    }

    char* short_s1 = strndup(s1 + pref, j1 + 1 - pref);
    char* short_s2 = strndup(s2 + pref, j2 + 1 - pref);
    
    pair lcs[MAX_LENGTH];
    int len_lcs;
    find_lcs(short_s1, short_s2, lcs, &len_lcs);

    for (int i = 0; i <= len_lcs + 1; i++) {
      lcs[i].x += pref;
      lcs[i].y += pref;
    }
    
    for (int i = 0; i <= len_lcs; i++)
      // Ignore trivial empty changes
      if (lcs[i + 1].x != lcs[i].x + 1 ||
	  lcs[i + 1].y != lcs[i].y + 1) {
	printf("(%d,%d)", lcs[i].x + 1, lcs[i + 1].x - lcs[i].x - 1);
	for (int j = lcs[i].x + 1; j < lcs[i + 1].x; j++)
	  printf("%c", s1[j]);
	printf("(%d,%d)", lcs[i].y + 1, lcs[i + 1].y - lcs[i].y - 1);
	for (int j = lcs[i].y + 1; j < lcs[i + 1].y; j++)
	  printf("%c", s2[j]);
      }
  }

  printf("\n");
  return 0;
}

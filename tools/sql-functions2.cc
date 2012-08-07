/**
 * This file extends mysql with the leven() function, a distance function for approximate searches.
 *
 * Instructions:
 * 
 * 1. Install the mysql-devel libraries. Instructions vary based on your system. For example, under Ubuntu you would run
 *
 *    sudo apt-get install libmysqlclient-dev
 *
 * 2. Compile the file.
 *
 *    gcc -fPIC -shared -I/usr/include/mysql -o sql-functions2.so sql-functions2.cc
 *
 * 3. Obtain root permissions and move sql-functions2.so to where your MySQL libraries reside. Under Ubuntu:
 *
 *    sudo mv sql-functions2.so /usr/lib/mysql/plugin
 *
 * 4. On some distributions, you will need to turn off apparmor to prevent a MySQL permission denied error (1126):
 *
 *    sudo /etc/init.d/apparmor stop
 *
 *    You can now restart apparmor.
 * 
 * 5. At the MySQL command line prompt:
 *
 *    create function leven returns integer soname "sql-functions2.so";
 * 
 *    If you later wish to drop it:
 *
 *    drop function leven;
 */

#include <ctype.h>
#include <stdlib.h>
#include <string.h>
#include <mysql/my_global.h>
#include <mysql/my_sys.h>
#include <mysql/mysql.h>
#include <mysql/m_ctype.h>
#include <mysql/mysql_com.h>
#include <mysql/m_ctype.h>

#define COST_INS 5
#define COST_DEL 5
#define COST_TRANSPOSE 5
#define DIST_HORIZ 5
#define DIST_VERT 10
#define DIST_OTHER 15
#define LENGTHDIFF 2
#define INFTY 100000
#define MIN(X,Y) ((X) < (Y) ? (X) : (Y))
#define MAX(X,Y) ((X) > (Y) ? (X) : (Y))

#ifdef HAVE_DLOPEN

									// a  b  c  d  e  f  g  h  i  j  k  l  m  n  o  p  q  r  s  t  u  v  w  x  y  z	
	int coordx[26] = { 0, 4, 2, 2, 2, 3, 4, 5, 7, 6, 7, 8, 6, 5, 8, 9, 0, 3, 1, 4, 6, 3, 1, 1, 5, 0 }; // x coordinate of 'a' to 'z' letters
	int coordy[26] = { 1, 2, 2, 1, 0, 1, 1, 1, 0, 1, 1, 1, 2, 2, 0, 0, 0, 0, 1, 0, 0, 2, 0, 2, 0, 2 }; // y coordinate of 'a' to 'z' letters
	int a[100][100];

// Returns the length of the UTF8 string
int convertToUtf8(char* s, int len, unsigned* output) {
  
	int ulen = 0;
  int i = 0;

  while (i < len) {
    unsigned c = (unsigned char)s[i];
    if (c >> 7 == 0) {
      // 0vvvvvvv
      output[ulen++] = c;
      i++;
    } else if (c >> 5 == 6) {
      // 110vvvvv 10vvvvvv
      output[ulen++] = ((c & 0x1F) << 6) |
        ((unsigned)s[i + 1] & 0x3F);
      i += 2;
    } else if (c >> 4 == 14) {
      // 1110vvvv 10vvvvvv 10vvvvvv
      output[ulen++] = ((c & 0x0F) << 12) |
        (((unsigned)s[i + 1] & 0x3F) << 6) |
        ((unsigned)s[i + 2] & 0x3F);
      i += 3;
    } else if (c >> 3 == 30) {
      // 11110vvv 10vvvvvv 10vvvvvv 10vvvvvv
      output[ulen++] = ((c & 0x0F) << 18) |
        (((unsigned)s[i + 1] & 0x3F) << 12) |
        (((unsigned)s[i + 2] & 0x3F) << 6) |
        ((unsigned)s[i + 3] & 0x3F);
      i += 4;
    } else {
      // dunno, skip it
      i++;
    }
  }

  return ulen;
}

// u1 and u2 are Unicode codes
int equalIgnoreDiacritics(int u1, int u2) {
  if (u1 == 97 || u1 == 259 || u1 == 226) {
    return u2 == 97 || u2 == 259 || u2 == 226;
  }
  if (u1 == 105 || u1 == 238) {
    return (u2 == 105 || u2 == 238);
  }
  if (u1 == 115 || u1 == 351) {
    return (u2 == 115 || u2 == 351);
  }
  if (u1 == 116 || u1 == 355) {
    return (u2 == 116 || u2 == 355);
  }
  return (u1 == u2);
}

// Converts diacritics to ASCII and lower case
void convertASCII_tolower(unsigned* u, int ulen) {
	for (int i = 0; i < ulen; i++) {
		switch (u[i]) {
			case 259: u[i] = 'a'; break; // ă
			case 238: u[i] = 'i'; break; // î
			case 226: u[i] = 'a'; break; // â
			case 537: u[i] = 's'; break; // ș
			case 539: u[i] = 't'; break; // ț
			case 258: u[i] = 'A'; break; // Ă
			case 206: u[i] = 'I'; break; // Î
			case 194: u[i] = 'A'; break; // Â
			case 536: u[i] = 'S'; break; // Ș
			case 538: u[i] = 'T'; break; // Ț
		}
	}
	for (int i = 0; i < ulen; i++)
		u[i] = tolower(u[i]);
}

// Computes the distance between two letters
inline int distance(char c1, char c2) {
	if (c1 == c2)
		return 0;
	if (c1 < 'a' || c1 > 'z' || c2 < 'a' || c2 > 'z')
		return DIST_OTHER;
	if ((c1 == 'a' && c2 == 'e') || (c1 == 'e' && c2 == 'a')) 
		return DIST_HORIZ;
	if (coordy[c1 - 'a'] == coordy[c2 - 'a'] && abs(coordx[c1 - 'a'] - coordx[c2 - 'a']) == 1)
		return DIST_HORIZ;
	if ( (coordx[c1 - 'a'] == coordx[c2 - 'a'] && abs(coordy[c1 - 'a'] - coordy[c2 - 'a']) == 1) ||
			 (coordx[c1 - 'a'] - coordx[c2 - 'a'] + coordy[c1 - 'a'] - coordy[c2 - 'a'] == 0) )
		return DIST_VERT;
	else
		return DIST_OTHER;
}

// leven: returns true if the distance between the searched word and the 
// one in the dictionary is less than a maximum given as parameter
extern "C" {
  my_bool leven_init(UDF_INIT *initid, UDF_ARGS *args, char *message);
  long long leven(UDF_INIT *initid, UDF_ARGS *args, char *is_null,
		  char *error);
}

my_bool leven_init(UDF_INIT *initid, UDF_ARGS *args, char *message) {
  if (args->arg_count != 3 ||
      args->arg_type[0] != STRING_RESULT ||
      args->arg_type[1] != STRING_RESULT ||
			args->arg_type[2] != INT_RESULT) {
    strcpy(message, "leven requires 3 parameters: the searched word, the word in the dictionary and the maximum difference (maxdif)");
    return 1;
  }
  initid->maybe_null = 1;
  initid->decimals = 0;
  initid->max_length = 1;
  return 0;
}

long long leven(UDF_INIT *initid, UDF_ARGS *args, char *is_null, char *error) {
  char *s1 = args->args[0];
  char *s2 = args->args[1];
	long long maxdif = *((long long*) args->args[2]);
	int len1 = args->lengths[0];
  int len2 = args->lengths[1];

  unsigned u1[50], u2[50];
  int u1len = convertToUtf8(s1, len1, u1);
  int u2len = convertToUtf8(s2, len2, u2);

  if (abs(u1len - u2len) > LENGTHDIFF) {
   return 0; // Strings are clearly too different
  }

	convertASCII_tolower(u1, u1len);
	convertASCII_tolower(u2, u2len);

	int ovfmin, ovfmax, min, max, i, j;

	// Initialize Levenshtein matrix
	for (i = 0; i <= u1len; i++) 
	  for (j = 0; j <= u2len; j++) 
			a[i][j] = INFTY;	

	// Initialize first row and column
	for (i = 0; i <= u1len; i++) {
    a[i][0] = i * DIST_OTHER;
  }
  for (j = 0; j <= u2len; j++) {
    a[0][j] = j * COST_DEL;
  }
	
	// Compute the rest of the matrix with the custom Levenshtein algorithm
  for (i = 0; i < u1len; i++) {
		if (i + 1 - LENGTHDIFF < 1) {
			min = 0;
			ovfmin = 1;
		}
		else {
		 	min = i - LENGTHDIFF;
			ovfmin = 0;
		}
		if (i + 1 + LENGTHDIFF > u2len) {
			max = u2len;
			ovfmax = 1;
		}
		else {
			max = i + 1 + LENGTHDIFF;		
			ovfmax = 0;
		}
    for (j = min; j < max; j++) {

      int mati = i + 1, matj = j + 1;

	   	// Delete
			if (ovfmin == 1 || (ovfmin == 0 && j > min))
	      a[mati][matj] = a[mati][matj - 1] + COST_DEL;

      // Insert
			if (ovfmax == 1 || (ovfmax == 0 && j + 1 < max)) {
	      int costInsert = (i == 0) ? INFTY : MAX(COST_INS, distance(u1[i], u1[i - 1])); // At least COST_INS
	      a[mati][matj] = MIN(a[mati][matj], a[mati - 1][matj] + costInsert);
			}

      // Modify (This includes the case where u1[i] == u2[j] because dist(x, x) returns 0)
      a[mati][matj] = MIN(a[mati][matj], a[mati - 1][matj - 1] + distance(u1[i], u2[j]));
		
      // Transpose
      if (i && j && (u1[i] == u2[j - 1]) && (u1[i - 1] == u2[j])) {
				a[mati][matj] = MIN(a[mati][matj], a[mati - 2][matj - 2] + COST_TRANSPOSE);
      }
    }
  }

	if (a[u1len][u2len] <= maxdif)
		return a[u1len][u2len];
	return 0;
}

#endif /* HAVE_DLOPEN */

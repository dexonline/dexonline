/**
 * This file extends mysql with the dist2() function, a poor but fast distance function for approximate searches.
 *
 * Instructions:
 * 
 * 1. Install the mysql-devel libraries. Instructions vary based on your system. For example, under Ubuntu you would run
 *
 *    sudo apt-get install libmysqlclient-dev
 *
 * 2. Compile the file.
 *
 *    gcc -fPIC -shared -I/usr/include/mysql -o sql-functions.so sql-functions.cc
 *
 * 3. Obtain root permissions and move sql-functions.so to where your MySQL libraries reside. Under Ubuntu:
 *
 *    sudo mv sql-functions.so /usr/lib/mysql/plugin
 *
 * 4. On some distributions, you will need to turn off apparmor to prevent a MySQL permission denied error (1126):
 *
 *    sudo /etc/init.d/apparmor stop
 *
 *    You can now restart apparmor.
 * 
 * 5. At the MySQL command line prompt:
 *
 *    create function dist2 returns integer soname "sql-functions.so";
 * 
 *    If you later wish to drop it:
 *
 *    drop function dist2;
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

#ifdef HAVE_DLOPEN

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

// dist2: returns true if, after trimming the beginning and ending matching
// portions of the strings, the remainder has at most one character.
extern "C" {
  my_bool dist2_init(UDF_INIT *initid, UDF_ARGS *args, char *message);
  long long dist2(UDF_INIT *initid, UDF_ARGS *args, char *is_null,
		  char *error);
}

my_bool dist2_init(UDF_INIT *initid, UDF_ARGS *args, char *message) {
  if (args->arg_count != 2 ||
      args->arg_type[0] != STRING_RESULT ||
      args->arg_type[1] != STRING_RESULT) {
    strcpy(message, "DIST2 requires 2 strings");
    return 1;
  }
  
  initid->maybe_null=1;
  initid->decimals=0;
  initid->max_length=1;
  return 0;
}

long long dist2(UDF_INIT *initid, UDF_ARGS *args, char *is_null, char *error) {
  char *s1 = args->args[0];
  char *s2 = args->args[1];
  int len1 = args->lengths[0];
  int len2 = args->lengths[1];

  unsigned u1[50], u2[50];
  int u1len = convertToUtf8(s1, len1, u1);
  int u2len = convertToUtf8(s2, len2, u2);

  if (abs(u1len - u2len) > 1) {
    return 0; // Strings are clearly too different
  }

  int i = 0;
  while (i < u1len && i < u2len && equalIgnoreDiacritics(u1[i], u2[i])) {
    i++;
  }

  if (i == u1len || i == u2len) {
    return 1;
  }

  int j1 = u1len - 1;
  int j2 = u2len - 1;
  while (j1 >= i && j2 >= i && equalIgnoreDiacritics(u1[j1], u2[j2])) {
    j1--;
    j2--;
  }

  return j1 <= i && j2 <= i;
}

#endif /* HAVE_DLOPEN */

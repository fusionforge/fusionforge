/*
 * Small Tool to move files from the FTP incoming dir
 * to the project ftp space.  Runs +suid.
 *
 * $Id: fileforge.c,v 1.5 2000/08/14 23:50:26 msnelham Exp $
 */
#include <stdlib.h>
#include <stdio.h>
#include <unistd.h>
#include <sys/stat.h>
#include <sys/types.h>
#include <fcntl.h>
#include <unistd.h>
#include <errno.h>
#include <string.h>

int legal_string (char* test_string) {

  /* test for legal characters:
     -./0-9  45-57
     A-Z     65-90
     a-z     97-122
   */

  int i;

  for (i = 0; i < strlen(test_string); i++) {
    if ( (test_string[i] < 43) || (test_string[i] == 44) ||
	 ((test_string[i] > 57) && (test_string[i] < 65)) ||
	 ((test_string[i] != 95) && (test_string[i] > 90) && (test_string[i] < 97)) ||
	 (test_string[i] > 122) ) {
      printf("%c", test_string[i]);
      return 0;
    } /* if */
  } /* for */

  /* test for illegal combinations of legal characters: ".." */
  if (strstr(test_string, "..")) {
    return 0;
  } /* if */

  return 1;
} /* legal_string */

int main (int argc, char** argv) {

  /* edit me */
  char* src_dir_root   = "/var/lib/sourceforge/chroot/home/users/";
  char* src_dir_incoming = "/incoming/";
  char* dest_dir_root  = "/var/lib/sourceforge/download/";
  char* dest_dir_download = "/";

  /* don't edit me (unless mv isn't in /bin) */
  char* move_path = "/bin/mv";
  char* move_file = "mv";
  char* dest_file;
  char* src_file;

  if (argc != 4) {
    fprintf(stderr, "FAILURE: usage: fileforge file user group\n");
    exit(1);
  } /* if */
  else {
    /* set source */
    src_file = (char *) malloc(strlen(src_dir_root) + strlen(src_dir_incoming) + strlen(argv[1]) +  strlen(argv[2]) + 1 );
    strcpy(src_file, src_dir_root);
    strcat(src_file, argv[2]);
    strcat(src_file, src_dir_incoming);
    strcat(src_file, argv[1]);

    /* set destination */
    dest_file = (char *) malloc(strlen(dest_dir_root) + strlen(dest_dir_download) + strlen(argv[3]) + 1);
    strcpy(dest_file, dest_dir_root);
    strcat(dest_file, argv[3]);
    strcat(dest_file, dest_dir_download);

    /* test for legal characters: [a-zA-Z0-9_-.]  */
    /* test for illegal combinations of legal characters: ".." */
    if (!legal_string(src_file)) {
      fprintf(stderr,"SRC=%s\nDEST=%s\n",src_file,dest_file);
      fprintf(stderr, "FAILURE: illegal characters in source file\n");
      exit(1);
    } /* if */

    if (!legal_string(dest_file)) {
      fprintf(stderr,"SRC=%s\nDEST=%s\n",src_file,dest_file);
      fprintf(stderr, "FAILURE: illegal characters in destination file\n");
      exit(1);
    } /* if */

    if ((mkdir(dest_file, 0775) != 0) && errno != EEXIST) {
      fprintf(stderr,"SRC=%s\nDEST=%s\n",src_file,dest_file);
      fprintf(stderr, "FAILURE: destination directory could not be created\n");
      exit(1);
    } /* if */

    /* exec it */
    if (execl(move_path, move_file, src_file, dest_file, (char *)0) == -1) {
      fprintf(stderr,"SRC=%s\nDEST=%s\n",src_file,dest_file);
      perror("FAILURE");
      exit(1);
    } /* if */
  } /* else */

  /* printf("OK\n"); */
  free(dest_file);
  free(src_file);
  exit(0);
} /* main */

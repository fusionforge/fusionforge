#!/usr/local/bin/python
import sys
import os
import string
import time
import commands

def main():
	fp2 = open("/etc/mail/aliases.mailman","a+")
	fp = sys.stdin
	while 1:
		alias = fp.readline()
		if not alias:
			break
#		print "writing alias", alias
		fp2.write(alias)
	fp.close()
	fp2.close()

if __name__ == '__main__':
    main()

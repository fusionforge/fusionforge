#! /bin/sh

python <<EOF
import sys
import os
import string
import ConfigParser
r = ConfigParser.RawConfigParser ()
r.read ("/etc/viewcvs/viewcvs.conf")
r.set ("general","svn_roots","")
l = os.listdir ("/var/lib/gforge/chroot/svnroot")
l2 = []
for d in l:
  l2.append (d + ": svn://localhost/" + d)
r.set ("general","svn_roots",string.join (l2, ', '))
r.set ("general","cvs_roots","")
if len(l) == 0:
  sys.exit("No svn repository found. I dunno what to set for default_root")
r.set ("general","default_root", l [0])
r.write (open ("/var/lib/gforge/etc/viewcvs.conf", "w"))
EOF

sed 's,pathname = CONF_PATHNAME .*,pathname = "/var/lib/gforge/etc/viewcvs.conf",' \
    /usr/lib/python2.3/site-packages/viewcvs/viewcvs.py > /var/lib/gforge/etc/viewcvs.py

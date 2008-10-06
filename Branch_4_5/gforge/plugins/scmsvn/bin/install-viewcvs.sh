#! /bin/sh

# Create ViewCVS config for GForge from the own ViewCVS conf file.
# Should be launched each time you change the ViewCVS conf is modified.

server_admin=$(grep ^server_admin= /etc/gforge/gforge.conf | cut -d= -f2-)
gforge_chroot=$(grep ^gforge_chroot= /etc/gforge/gforge.conf | cut -d= -f2-)
svndir=$(grep ^svndir= /etc/gforge/gforge.conf | cut -d= -f2-)
cvsdir=$(grep ^cvsdir= /etc/gforge/gforge.conf | cut -d= -f2-)

python <<EOF
import sys
import os
import string
import ConfigParser
r = ConfigParser.RawConfigParser ()
r.read ("/etc/viewcvs/viewcvs.conf")
r.set ("general","svn_roots","")
r.set ("general","cvs_roots","")
r.set ("general","root_parents","$gforge_chroot/$svndir : svn")
# uncomment the next line if you want to use ViewCVS to browse CVS repositories
#r.set ("general","root_parents","$gforge_chroot/$svndir : svn, $gforge_chroot/$cvsdir : cvs")
r.set ("general","address","<a href=\"mailto:$server_admin\">CVS/SVN Admin</a>");
r.set ("general","default_root", "")
r.set ("templates","query","/etc/gforge/plugins/scmsvn/viewcvs/templates/query.ezt")
r.set ("templates","diff","/etc/gforge/plugins/scmsvn/viewcvs/templates/diff.ezt")
r.set ("templates","graph","/etc/gforge/plugins/scmsvn/viewcvs/templates/graph.ezt")
r.set ("templates","annotate","/etc/gforge/plugins/scmsvn/viewcvs/templates/annotate.ezt")
r.set ("templates","markup","/etc/gforge/plugins/scmsvn/viewcvs/templates/markup.ezt")
r.set ("templates","revision","/etc/gforge/plugins/scmsvn/viewcvs/templates/revision.ezt")
r.set ("templates","query_form","/etc/gforge/plugins/scmsvn/viewcvs/templates/query_form.ezt")
r.set ("templates","query_results","/etc/gforge/plugins/scmsvn/viewcvs/templates/query_results.ezt")
r.set ("templates","error","/etc/gforge/plugins/scmsvn/viewcvs/templates/error.ezt")
r.set ("templates","directory","/etc/gforge/plugins/scmsvn/viewcvs/templates/directory.ezt")
r.set ("templates","log","/etc/gforge/plugins/scmsvn/viewcvs/templates/log_table.ezt")
r.set ("options", "generate_etags", 0)
r.set ("options","docroot","/plugins/scmsvn/viewcvs");
r.set ("options","icons","/plugins/scmsvn/viewcvs/icons");
r.write (open ("/var/lib/gforge/etc/viewcvs.conf", "w"))
EOF

sed 's,pathname = CONF_PATHNAME .*,pathname = "/var/lib/gforge/etc/viewcvs.conf",' \
    /usr/lib/python2.3/site-packages/viewcvs/viewcvs.py > /var/lib/gforge/etc/viewcvs.py

echo "Created ViewCVS conf for GForge - make sure to rerun $0 \
each time you change the ViewCVS conf"

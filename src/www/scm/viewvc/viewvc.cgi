#!/usr/bin/env python
# Locate ViewVC and run it
# (local CGI version, see also plugins/scmsvn/libexec/viewvc.cgi)

import sys
import os
import glob

LIBRARY_GLOBS = (
  r"/usr/lib/viewvc/lib",  # Debian
  r"/usr/lib/python2.?/site-packages/viewvc/lib",  # Fedora/CentOS
  r"/srv/viewvc/lib",  # openSUSE
  r"/usr/share/viewvc/lib",  # mageia
)
CONF_GLOBS = (
  r"/etc/viewvc/viewvc.conf",  # Debian/Fedora/CentOS/mageia
  r"/srv/viewvc/viewvc.conf",  # openSUSE
)

for pat in LIBRARY_GLOBS:
  if glob.glob(pat):
    LIBRARY_DIR=glob.glob(pat)[0]
    break
sys.path.insert(0, LIBRARY_DIR)

for pat in CONF_GLOBS:
  if glob.glob(pat):
    CONF_PATHNAME=glob.glob(pat)[0]
    break
#CONF_PATHNAME = os.path.dirname(__filename__) + '/viewvc.conf'


import sapi
import viewvc

server = sapi.CgiServer()
cfg = viewvc.load_config(CONF_PATHNAME, server)

# Read the repository root dir from the environment.
# This way, we will only have ONE repository configured (the one we're browsing). This
# is more secure than having one (CVS|SVN) root configured with all the repositories inside

if os.environ["REPOSITORY_TYPE"] == 'cvs':
  cfg.general.cvs_roots[os.environ["REPOSITORY_NAME"]] = os.environ["REPOSITORY_ROOT"]
elif os.environ["REPOSITORY_TYPE"] == 'svn':
  cfg.general.svn_roots[os.environ["REPOSITORY_NAME"]] = os.environ["REPOSITORY_ROOT"]

cfg.general.address = "root@" + os.environ["HTTP_HOST"]
cfg.options.docroot = os.environ["DOCROOT"]
#cfg.options.allow_compress = False
#cfg.options.generate_etags = False
#cfg.options.allowed_views = ['annotate', 'diff', 'markup', 'roots', 'tar', 'co']

viewvc.main(server, cfg)

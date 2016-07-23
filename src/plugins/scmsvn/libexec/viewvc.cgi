#!/usr/bin/env python
# Locate ViewVC and run it

import sys
import os
import glob

LIBRARY_GLOBS = (
  r'/usr/lib/viewvc/lib',  # Debian
  r'/usr/lib/python2.?/site-packages/viewvc/lib',  # Fedora/CentOS
  r'/srv/viewvc/lib',  # openSUSE
  r'/usr/share/viewvc/lib',  # mageia
)
CONF_GLOBS = (
  r'/etc/viewvc/viewvc.conf',  # Debian/Fedora/CentOS/mageia
  r'/srv/viewvc/viewvc.conf',  # openSUSE
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

#print "Content-type: text/plain\n";
#print os.popen('id').read()
#print os.environ
#sys.exit(0)


import sapi
import viewvc

server = sapi.CgiServer()
cfg = viewvc.load_config(CONF_PATHNAME, server)


import subprocess

# Get repo path from FusionForge config
# couldn't find any way to disable compression in forge_get_config/PHP >(
encoding = os.environ.get('HTTP_ACCEPT_ENCODING', None)
if 'HTTP_ACCEPT_ENCODING' in os.environ: del os.environ['HTTP_ACCEPT_ENCODING']
repos_path = subprocess.check_output(['forge_get_config', 'repos_path', 'scmsvn']).rstrip()
cfg.general.root_parents = [repos_path+': svn']

# Authentify request
try:
  if not os.environ['REQUEST_URI'].startswith('/anonscm/'):
    web_host = subprocess.check_output(['forge_get_config', 'web_host']).rstrip()
    import pycurl
    from StringIO import StringIO
    buffer = StringIO()
    c = pycurl.Curl()
    c.setopt(c.URL, 'https://' + web_host + '/account/check_forwarded_session.php')
    c.setopt(c.SSL_VERIFYPEER, False)
    c.setopt(c.SSL_VERIFYHOST, False)
    c.setopt(c.COOKIE, os.environ.get('HTTP_COOKIE', ''))
    c.setopt(c.USERAGENT, os.environ.get('HTTP_USER_AGENT', ''))
    c.setopt(c.HTTPHEADER, ['X-Forwarded-For: '+os.environ.get('HTTP_X_FORWARDED_FOR', '')])
    c.setopt(c.WRITEFUNCTION, buffer.write)
    c.perform()
    c.close()
    body = buffer.getvalue()
    if body != 'OK':
      raise Exception('Unauthorized')
except Exception, e:
  print "Content-type: text/plain\n";
  print e
  #raise
  sys.exit(1)
if encoding != None: os.environ['HTTP_ACCEPT_ENCODING'] = encoding

# Pretend we're running on the source host
os.environ['SCRIPT_NAME'] = '/scm/viewvc.php'
cfg.options.docroot = '/scm/viewvc/docroot';

# Generic configuration
cfg.general.address = 'root@' + os.environ['HTTP_HOST']
#cfg.options.allow_compress = False
#cfg.options.generate_etags = False
#cfg.options.allowed_views = ['annotate', 'diff', 'markup', 'roots', 'tar', 'co']

viewvc.main(server, cfg)

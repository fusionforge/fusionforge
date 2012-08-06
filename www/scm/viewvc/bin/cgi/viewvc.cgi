#!/usr/bin/env python
# -*-python-*-
#
# Copyright (C) 1999-2006 The ViewCVS Group. All Rights Reserved.
#
# By using this file, you agree to the terms and conditions set forth in
# the LICENSE.html file which can be found at the top level of the ViewVC
# distribution or at http://viewvc.org/license-1.html.
#
# For more information, visit http://viewvc.org/
#
# -----------------------------------------------------------------------
#
# viewvc: View CVS/SVN repositories via a web browser
#
# -----------------------------------------------------------------------
#
# This is a teeny stub to launch the main ViewVC app. It checks the load
# average, then loads the (precompiled) viewvc.py file and runs it.
#
# -----------------------------------------------------------------------
#

# THIS CONFIGURATION FILE HAS BEEN MODIFIED WITH THE PURPOSE OF
# INTEGRATING VIEWVC WITH GFORGE.

#########################################################################
#
# INSTALL-TIME CONFIGURATION
#
# These values will be set during the installation process. During
# development, they will remain None.
#

#LIBRARY_DIR = None
#CONF_PATHNAME = None

#########################################################################
#
# Adjust sys.path to include our library directory
#

import sys
import os

#if LIBRARY_DIR:
#  sys.path.insert(0, LIBRARY_DIR)
#else:
#  sys.path.insert(0, os.path.abspath(os.path.join(sys.argv[0],
#                                                  "../../../lib")))

sys.path.insert(0, os.path.abspath(os.path.join(sys.argv[0], "../../../lib")))
CONF_PATHNAME = os.path.abspath(os.path.join(sys.argv[0], "../../../viewvc.conf"))


#########################################################################

### add code for checking the load average

#########################################################################

# go do the work
import sapi
import viewvc

server = sapi.CgiServer()

# read the main configuration
cfg = viewvc.load_config(CONF_PATHNAME, server)

# BEGIN OF GForge customization

# Read the repository root dir from the environment.
# This way, we will only have ONE repository configured (the one we're browsing). This 
# is more secure than having one (CVS|SVN) root configured with all the repositories inside

if os.environ["REPOSITORY_TYPE"] == 'cvs':
  cfg.general.cvs_roots[os.environ["REPOSITORY_NAME"]] = os.environ["REPOSITORY_ROOT"]
elif os.environ["REPOSITORY_TYPE"] == 'svn':
  cfg.general.svn_roots[os.environ["REPOSITORY_NAME"]] = os.environ["REPOSITORY_ROOT"]

cfg.general.address = "<a href='mailto:root@"+os.environ["HTTP_HOST"]+"'>root@" + os.environ["HTTP_HOST"]+ "</a>"
cfg.options.docroot = os.environ["DOCROOT"]

# END OF GForge customization

viewvc.main(server, cfg)

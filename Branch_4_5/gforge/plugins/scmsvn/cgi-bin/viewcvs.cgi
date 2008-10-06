#!/usr/bin/python
# -*-python-*-
#
# Copyright (C) 1999-2002 The ViewCVS Group. All Rights Reserved.
# Patched by Roland Mas <lolando@debian.org>
#
# By using this file, you agree to the terms and conditions set forth in
# the LICENSE.html file which can be found at the top level of the ViewCVS
# distribution or at http://viewcvs.sourceforge.net/license-1.html.
#
# Contact information:
#   Greg Stein, PO Box 760, Palo Alto, CA, 94302
#   gstein@lyra.org, http://viewcvs.sourceforge.net/
#
# -----------------------------------------------------------------------
#
# viewcvs: View CVS repositories via a web browser
#
# -----------------------------------------------------------------------

import os
import sys

sys.path.insert(0, "/usr/lib/python2.3/site-packages/viewcvs/")
sys.path.insert(0, "/var/lib/gforge/etc/")

# go do the work
import sapi
import viewcvs

os.umask(0002)
viewcvs.main(sapi.CgiServer())

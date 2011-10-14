# -*- coding: iso-8859-1 mode:python -*-
"""
    MoinMoin - mod_wsgi driver scrip for integration with FusionForge

    @copyright: 2011 by Roland Mas
    @license: GNU GPL, see COPYING for details.
"""

import sys, os

sys.path.insert(0, '/etc/gforge/plugins/moinmoin')
from MoinMoin.web.serving import make_application
application = make_application(shared=True)

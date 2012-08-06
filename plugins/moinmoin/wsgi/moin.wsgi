# -*- coding: iso-8859-1 mode:python -*-
"""
    MoinMoin - mod_wsgi driver scrip for integration with FusionForge

    @copyright: 2011 by Roland Mas
    @license: GNU GPL, see COPYING for details.
"""

import sys, os
from MoinMoin import log

sys.path.insert(0, '/var/lib/gforge/plugins/moinmoin/wikidata')
sys.path.insert(0, '/usr/share/gforge/plugins/moinmoin/lib')

log.load_config('/etc/fusionforge/plugins/moinmoin/moinmoin.conf')
from MoinMoin.web.serving import make_application
application = make_application(shared=True)

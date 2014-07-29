# -*- coding: iso-8859-1 mode:python -*-
"""
    MoinMoin - mod_wsgi driver script for integration with FusionForge

    @copyright: 2011 by Roland Mas
    @license: GNU GPL, see COPYING for details.
"""

import sys, os, re
from MoinMoin import log

config_path = os.popen('forge_get_config config_path').read().strip()
data_path = os.popen('forge_get_config data_path').read().strip()
source_path = os.popen('forge_get_config source_path').read().strip()

sys.path.insert(0, data_path + '/plugins/moinmoin/wikidata')
sys.path.insert(0, source_path + '/plugins/moinmoin/lib')

log.load_config(config_path + '/plugins/moinmoin/moinmoin.conf')
from MoinMoin.web.serving import make_application
application = make_application(shared=True)

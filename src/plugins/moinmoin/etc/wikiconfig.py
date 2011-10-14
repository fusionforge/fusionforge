# -*- coding: iso-8859-1 mode:python -*-

from MoinMoin.config import multiconfig
import fusionforge

import sys
sys.path.insert(0, '/etc/moin')
import farmconfig

class Config(farmconfig.FarmConfig):

    # basic options (you normally need to change these)
    sitename = u'ForgeWiki' # [Unicode]
    interwikiname = u'ForgeWiki' # [Unicode]

    # name of entry page / front page [Unicode], choose one of those:

    # a) if most wiki content is in a single language
    #page_front_page = u"MyStartingPage"

    # b) if wiki content is maintained in many languages
    page_front_page = u"FrontPage"

    # data_dir = '/var/lib/gforge/plugins/moinmoin/wikidata/data'
    # data_underlay_dir = '/var/lib/gforge/plugins/moinmoin/wikidata/underlay'

    ffsa = fusionforge.FusionForgeSessionAuth()

    auth = [ffsa]
    superuser = ffsa.get_super_users()

    ff_link = fusionforge.FusionForgeLink()
    ff_host = ff_link.get_config('web_host')
    farmconfig.wikis = []
    for project in ff_link.get_projects():
        farmconfig.wikis.append(("project", "^https?://"+ff_host+"/plugins/moinmoin/"+project+"/.*$"),)


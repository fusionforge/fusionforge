# -*- coding: iso-8859-1 mode:python -*-

from MoinMoin.config import multiconfig
class Config(multiconfig.DefaultConfig):

    # basic options (you normally need to change these)
    sitename = u'ForgeWiki' # [Unicode]
    interwikiname = u'ForgeWiki' # [Unicode]

    # name of entry page / front page [Unicode], choose one of those:

    # a) if most wiki content is in a single language
    #page_front_page = u"MyStartingPage"

    # b) if wiki content is maintained in many languages
    page_front_page = u"FrontPage"

    data_dir = '/var/lib/gforge/plugins/moinmoin/wikidata/data'
    data_underlay_dir = '/var/lib/gforge/plugins/moinmoin/wikidata/underlay'

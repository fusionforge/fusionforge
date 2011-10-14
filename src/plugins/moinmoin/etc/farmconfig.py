# -*- coding: iso-8859-1 mode:python -*-

from MoinMoin.config import multiconfig
import fusionforge

class FarmConfig(multiconfig.DefaultConfig):

    # basic options (you normally need to change these)
    sitename = u'ForgeWiki' # [Unicode]
    interwikiname = u'ForgeWiki' # [Unicode]

    page_front_page = u"FrontPage"

    ffsa = fusionforge.FusionForgeSessionAuth()

    auth = [ffsa]
    superuser = ffsa.get_super_users()

    acl_rights_default = u'All:'
    for i in superuser:
        acl_rights_default = i+':read,write,delete,revert,admin ' + acl_rights_default

ff_link = fusionforge.FusionForgeLink()
ff_host = ff_link.get_config('web_host')
wikis = []
for project in ff_link.get_projects():
    wikis.append((project, "^https?://"+ff_host+"/plugins/moinmoin/"+project+"/.*$"),)


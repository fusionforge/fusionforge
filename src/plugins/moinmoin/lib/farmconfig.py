# -*- coding: iso-8859-1 mode:python -*-

from MoinMoin.config import multiconfig
import fusionforge
import string

class FarmConfig(multiconfig.DefaultConfig):

    # basic options (you normally need to change these)
    sitename = u'ForgeWiki' # [Unicode]
    interwikiname = u'ForgeWiki' # [Unicode]

    page_front_page = u"FrontPage"

    ffsa = fusionforge.FusionForgeSessionAuth()
    ff_host = fusionforge.FusionForgeLink().get_config('web_host')

    auth = [ffsa]
    theme_default = 'mentalwealth'

    acl_rights_default = \
      string.join (map (lambda u: u+":read,write,delete,revert,admin",
                        ffsa.admins)
                   + ["All:"])

    def groups (self, request):
        from MoinMoin.datastruct import WikiGroups, CompositeGroups
        from ff_groups import FFLazyGroups
        return CompositeGroups (request,
                                FFLazyGroups (request, self.__class__.ffsa),
                                WikiGroups (request))

wikis = map (lambda p: \
               (p, "^https?://%s/plugins/moinmoin/%s.*$"
                   % (FarmConfig.ff_host, p)),
             FarmConfig.ffsa.projects)

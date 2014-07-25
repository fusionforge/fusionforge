# -*- coding: iso-8859-1 mode:python -*-

from MoinMoin.config import multiconfig
import fusionforge
import string
import logging
import os

class FarmConfig(multiconfig.DefaultConfig):

    # Common declarations

    page_front_page = u"FrontPage"
    theme_default = 'mentalwealth'

    session_cookies = ['forge_session_authbuiltin', 'forge_session_authcas', 'forge_session_authhttpd', 'forge_session_authldap', 'forge_session_authopenid', 'forge_session_authwebid']

    ffsa = fusionforge.FusionForgeSessionAuth(session_cookies)
    forge_get_config = fusionforge.FusionForgeLink(session_cookies).get_config
    ff_host = fusionforge.FusionForgeLink(session_cookies).get_config('web_host')

    auth = [ffsa]

    # Defaults (overridden per project)

    sitename = u'ForgeWiki' # [Unicode]
    interwikiname = u'ForgeWiki' # [Unicode]
    acl_rights_default = \
      string.join (map (lambda u: u+":read,write,delete,revert,admin",
                        ffsa.admins)
                   + ["All:"])

    def __init__(self, project_name):
        self.project_name = project_name
        self.sitename = u'%s' % project_name
        self.interwikiname = u'%s' % project_name
        self.data_dir = (self.__class__.forge_get_config('data_path') + '/plugins/moinmoin/wikidata/%s/data') % project_name
        self.data_underlay_dir = (self.__class__.forge_get_config('data_path') + '/plugins/moinmoin/wikidata/%s/underlay') % project_name

        page_header1_file = (self.__class__.forge_get_config('chroot') + '/home/groups/%s/plugins/moinmoin/page_header1.html') % project_name
        if os.path.exists(page_header1_file):
            with open(page_header1_file) as f:
                self.page_header1 = f.read()

        self.acl_rights_default = self.ffsa.get_moinmoin_acl_string(project_name)

        # Call inherited constructor once instance specific variables
        # have been set.

        multiconfig.DefaultConfig.__init__(self, project_name)

    def groups (self, request):
        from MoinMoin.datastruct import WikiGroups, CompositeGroups
        from ff_groups import FFLazyGroups
        return CompositeGroups (request,
                                FFLazyGroups (request, self.__class__.ffsa),
                                WikiGroups (request))

wikis = map (lambda p: \
               (p, "^https?://%s/plugins/moinmoin/%s/.*$"
                   % (FarmConfig.ff_host, p)),
             FarmConfig.ffsa.projects)

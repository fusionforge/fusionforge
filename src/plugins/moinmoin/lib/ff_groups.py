# -*- coding: iso-8859-1 -*-
"""
    MoinMoin - FusionForge groups backed

    This backend assigns group membership according to FusionForge user
    access.

    @copyright: 2009 MoinMoin:DmitrijsMilajevs
    @license: GPL, see COPYING for details
"""

from MoinMoin.datastruct.backends import LazyGroup, LazyGroupsBackend
import logging
import re

def parse_group_name(group_name):
    m = re.match \
      ("FF(Site(Admins|Users)|Project_(.*)_(Admins|Writers|Readers))Group$",
       group_name)
    if m:
      if m.group (1)[0:4] == 'Site':
        return ('Site', '', m.group (2))
      else:
        return ('Project', m.group (3), m.group (4))
    else:
      return None

class FFLazyGroup(LazyGroup):
    pass

class FFLazyGroups(LazyGroupsBackend):

    def __init__(self, request, ffsa):
        super(FFLazyGroups, self).__init__(request)

        self._ffsa = ffsa
        if request.user.valid:
          self._username = request.user.name
        else:
          self._username = None
        logging.debug ("FFLazyGroups __init__: username=%s", (self._username,))

    def __contains__(self, group_name):
        logging.debug \
          ("FFLazyGroups __contains__: group_name=%s", (group_name,))

        try:
          (scope, project, permission) = parse_group_name (group_name)
        except:
          return False

        if scope == "Site":
          return True
        elif scope == "Project":
          return project in self._ffsa.projects
        else:
          return False

    def __iter__(self):
        return reduce \
                   (lambda a, b: a+b,
                    [ [ 'SiteAdmins', 'SiteUsers' ] ]
                    + map (lambda p:
                              map (lambda r: 'FFProject_%s_%sGroup' % (p, r),
                                   [ 'Admins', 'Writers', 'Readers' ]),
                           self._projects))

    def __getitem__(self, group_name):
        return FFLazyGroup(self.request, group_name, self)

    def _iter_group_members(self, group_name):
        logging.debug \
          ("FFLazyGroups _iter_group_members: group_name=%s", (group_name,))
        try:
          (scope, project, permission) = parse_group_name (group_name)
        except:
          return None

        if scope == "Site":
          if permission == "User":
            # ??? iterator on all Forge users
            raise NotImplemented
          elif permission == "Admin":
            return self._ffsa.admins.__iter__ ()

        elif scope == "Project":
          try:
            return self._ffsa.get_permission_entries(project,permission).__iter__()
          except:
            return None

    def _group_has_member(self, group_name, member):
        logging.debug \
          ("FFLazyGroups _group_has_member: group_name=%s member=%s _user=%s" \
           % (group_name, member, self._username))

        # For anonymous (non-logged-in) users, member is "" and self._username
        # is None. For authenticated users, both are assumed to be set to
        # the user's login. If not, we consider that we have an unexpected
        # inconsistency, and return False.

        if member == "" or member != self._username:
            return False

        try:
          (scope, project, permission) = parse_group_name (group_name)
        except:
          logging.debug \
            ("FFLazyGroups _group_has_member: False (can't parse)")
          return False

        logging.debug \
          ("FFLazyGroups _group_has_member: scope=%s project=%s perm=%s" \
           % (scope, project, permission))

        result = False
        if scope == "Site":
          if permission == "Users":
            result = self._username != None
          elif permission == "Admins":
            # ??? ffsa should instead provide is_super_user
            result = self._username in self._ffsa.admins

        elif scope == "Project":
          result = self._ffsa.check_permission (project, permission, self._username)

        logging.debug \
          ("FFLazyGroups _group_has_member: %s" % (result,))
        return result

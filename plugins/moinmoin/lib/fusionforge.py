# -*- coding: iso-8859-1 -*-
"""
    MoinMoin - FusionForge session cookie authentication

    @copyright: 2005 MoinMoin:AlexanderSchremmer (Thanks to Spreadshirt)
    @copyright: 2011 Roland Mas
    @license: GNU GPL, see COPYING for details.
"""

import base64
import hashlib
import logging
import psycopg2
import re
import string
import subprocess
import urllib

from MoinMoin import user
from MoinMoin.auth import _PHPsessionParser, BaseAuth

perm_name = "plugin_moinmoin_access"
perm_map = { "Admins":  3,
             "Writers": 2,
             "Readers": 1 }

class FusionForgeError(Exception):
    def __init__(self,  msg):
        Exception.__init__(self, msg)
        self.msg = msg

    def __str__(self):
        return "%s\n" % self.msg


class FusionForgeLink():
    def get_config(self, varname, secname='core'):
        if secname not in self.cachedconfig:
            self.cachedconfig[secname] = {}
        if varname not in self.cachedconfig[secname]:
            self.cachedconfig[secname][varname] = \
              subprocess.Popen(["/usr/share/gforge/bin/forge_get_config",
                               varname, secname],
                stdout=subprocess.PIPE).communicate()[0].rstrip('\n')
        return self.cachedconfig[secname][varname]

    def __init__(self, cookies=['session_ser'], autocreate=True):
        self.cachedconfig = {}
        self.database_host = self.get_config('database_host')
        self.database_name = self.get_config('database_name')
        self.database_user = self.get_config('database_user')
        self.database_port = self.get_config('database_port')
        self.database_password = self.get_config('database_password')
        if (self.database_host != ''):
            self._conn = psycopg2.connect(database=self.database_name,
                                          user=self.database_user,
                                          port=self.database_port,
                                          password=self.database_password,
                                          host=self.database_host)
        else:
            self._conn = psycopg2.connect(database=self.database_name,
                                          user=self.database_user,
                                          port=self.database_port,
                                          password=self.database_password)

        # We never want to start transactions, all accesses here are read-only
        # anyway.
        self._conn.set_isolation_level \
          (psycopg2.extensions.ISOLATION_LEVEL_AUTOCOMMIT)
        logging.debug ("FusionForgeLink: __init__ done")

    def __del__(self):
        self._conn.close ()

class FusionForgeSessionAuth(BaseAuth):
    """ FusionForge session cookie authentication """

    name = 'fusionforge_session'
    logout_possible = False

    def __init__(self, cookies=['session_ser'], autocreate=True):
        """ @param cookie: Names of the cookies to parse.
        """
        BaseAuth.__init__(self)
        self.cookies = cookies
        self.autocreate = autocreate

        self.fflink = FusionForgeLink()
        self.session_key = self.fflink.get_config('session_key')

        # List super users (Forge admins)

        conn = self.fflink._conn
        cur = conn.cursor()
        cur.execute("""SELECT distinct(u.user_name)
                       FROM users u,
                            pfo_user_role pur,
                            pfo_role pr,
                            pfo_role_setting prs
                       WHERE u.user_id = pur.user_id
                         AND pur.role_id = pr.role_id
                         AND pr.role_id = prs.role_id
                         AND prs.section_name='forge_admin'
                         AND prs.perm_val = 1""")
        self.admins = []
        if cur.rowcount > 0:
           self.admins = [r[0] for r in cur]
        logging.debug ("FusionForgeSessionAuth: admins=%s", (self.admins,))

        # List projects

        cur.execute("""SELECT g.unix_group_name
                       FROM groups g, group_plugin gp, plugins p
                       WHERE g.group_id = gp.group_id
                         AND gp.plugin_id = p.plugin_id
                         AND p.plugin_name = 'moinmoin'""")
        self.projects = []
        if cur.rowcount:
           self.projects = [r[0] for r in cur]
        logging.debug ("FusionForgeSessionAuth: projects=%s", (self.projects,))

    def get_moinmoin_acl_string(self, project_name):
        conn = self.fflink._conn
        cur = conn.cursor()

        # Get perm setting for anonymous users

        query = """SELECT prs.perm_val
                        FROM pfo_role_setting prs, groups
                       WHERE prs.role_id = 1
                         AND prs.section_name = '%s'
                         AND groups.unix_group_name = '%s'
                         AND prs.ref_id = groups.group_id""" \
                    % (perm_name, project_name)
        val = cur.execute(query)
        val = cur.fetchone()
        if val:
            anon_perm = [k for k, v in perm_map.iteritems() if v == val[0]]
            if anon_perm:
                anon_perm = anon_perm[0]
        else:
            anon_perm = None

        rights_dict = \
                      { 'Admins':  'read,write,delete,revert,admin',
                        'Writers': 'read,write,delete,revert',
                        'Readers': 'read' }

        rights = [ 'FFSiteAdminsGroup:read,write,delete,revert,admin' ] \
               + map (lambda (g, right):
                        'FFProject_%s_%sGroup:%s' % (project_name, g, right),
                        rights_dict.iteritems ())

        if anon_perm and rights_dict.has_key(anon_perm):
            rights.append ('All:' + rights_dict[anon_perm])
        else:
            rights.append ('All:')

        logging.debug ("FusionForgeSessionAuth.get_moinmoin_acl_string: %s",
                       (rights,))
        return string.join (rights)

    def get_permission_entries (self, project_name, perm, user_name = None):
        conn = self.fflink._conn
        cur = conn.cursor()

        if user_name:
            ucond = "u.user_name = '%s'" % (user_name)
        else:
            ucond = "TRUE"

        # ??? query should also include user if permission is granted to
        # authenticated non-members.

        query = """SELECT DISTINCT(u.user_name)
                        FROM users u, pfo_user_role pur, pfo_role pr,
                             pfo_role_setting prs, groups
                       WHERE %s
                         AND ((u.user_id = pur.user_id
                               AND pur.role_id = pr.role_id
                               AND pr.role_id = prs.role_id)
                              OR prs.role_id = 2)
                         AND prs.section_name = '%s'
                         AND groups.unix_group_name = '%s'
                         AND prs.perm_val = %d
                         AND prs.ref_id = groups.group_id""" \
                    % (ucond, perm_name, project_name, perm_map[perm])
        logging.debug ("get_perm_entries: " + query)
        cur.execute(query)
        result = []
        if cur.rowcount > 0:
            result = [u[0] for u in cur]
        logging.debug (" -> %s " % (result,))
        return result

    def check_permission (self, project_name, perm, user_name):
        return len(self.get_permission_entries \
                     (project_name, perm, user_name)) > 0

    def request(self, request, user_obj, **kw):
        cookies = kw.get('cookie')
        if cookies is None or cookies == {}:
            return None, False

        for cookiename in cookies:
            if cookiename not in self.cookies:
                continue
            cookievalue = \
              urllib.unquote(cookies[cookiename]).decode('iso-8859-1')

            m = re.search('(.*)-\*-(.*)', cookievalue)
            if m is None:
                continue
            (sserial, shash) = m.group(1, 2)

            sdata = base64.b64decode(sserial)
            if hashlib.md5(sdata + self.session_key).hexdigest() != shash:
                continue

            m = re.search('(.*)-\*-(.*)-\*-(.*)-\*-(.*)', sdata)
            if m is None:
                continue
            (user_id, time, ip, user_agent) = m.group(1, 2, 3, 4)

            conn = self.fflink._conn
            cur = conn.cursor()
            cur.execute("""SELECT user_name, realname
                           FROM users WHERE user_id=%s""", [user_id])
            (loginname, realname) = cur.fetchone()
            cur.close()

            # MoinMoin doesn't enforce unicity of realnames
            u = user.User(request, name=loginname, auth_username=loginname,
                          auth_method=self.name)

            if u and self.autocreate:
                u.create_or_update(True)
            if u and u.valid:
                self.auth_user = u
                return u, False
        self.auth_user = None
        return None, False

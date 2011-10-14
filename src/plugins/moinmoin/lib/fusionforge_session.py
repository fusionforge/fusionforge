# -*- coding: iso-8859-1 -*-
"""
    MoinMoin - FusionForge session cookie authentication

    @copyright: 2005 MoinMoin:AlexanderSchremmer (Thanks to Spreadshirt)
    @copyright: 2011 Roland Mas
    @license: GNU GPL, see COPYING for details.
"""

import urllib
import re
import hashlib
import base64
import subprocess
import psycopg2
from MoinMoin import user
from MoinMoin.auth import _PHPsessionParser, BaseAuth

class FusionForgeSessionAuth(BaseAuth):
    """ FusionForge session cookie authentication """

    name = 'fusionforge_session'

    def __getconfig(self, varname):
        return subprocess.Popen(["/usr/share/gforge/bin/forge_get_config", varname], stdout = subprocess.PIPE).communicate()[0].rstrip('\n')

    def __init__(self, cookies=['session_ser'], autocreate=True):
        """ @param cookie: Names of the cookies to parse.
        """
        BaseAuth.__init__(self)
        self.cookies = cookies
        self.autocreate = autocreate

        self.database_host = self.__getconfig('database_host')
        self.database_name = self.__getconfig('database_name')
        self.database_user = self.__getconfig('database_user')
        self.database_port = self.__getconfig('database_port')
        self.database_password = self.__getconfig('database_password')
        self.session_key = self.__getconfig('session_key')

        if (self.database_host != ''):
            self.conn = psycopg2.connect(database=self.database_name,
                                         user=self.database_user,
                                         port=self.database_port,
                                         password=self.database_password,
                                         host=self.database_host)
        else:
            self.conn = psycopg2.connect(database=self.database_name,
                                         user=self.database_user,
                                         port=self.database_port,
                                         password=self.database_password)

    def request(self, request, user_obj, **kw):
        cookies = kw.get('cookie')
        if cookies is None or cookies == {}:
            return user_obj, False

        for cookiename in cookies:
            if cookiename not in self.cookies:
                continue
            cookievalue = urllib.unquote(cookies[cookiename]).decode('iso-8859-1')

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

            cur = self.conn.cursor()
            cur.execute("SELECT user_name, realname FROM users WHERE user_id=%s", [user_id])
            (loginname, realname) = cur.fetchone()
            cur.close()

            # MoinMoin doesn't enforce unicity of realnames
            u = user.User(request, name=loginname, auth_username=loginname,
                          auth_method=self.name)

            if u and self.autocreate:
                u.create_or_update(True)
            if u and u.valid:
                return u, True # True to get other methods called, too
            return user_obj, False # continue with next method in auth list


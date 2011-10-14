 -*- coding: iso-8859-1 -*-
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
from MoinMoin import user
from MoinMoin.auth import _PHPsessionParser, BaseAuth

class FusionForgeSessionAuth(BaseAuth):
    """ FusionForge session cookie authentication """

    name = 'fusionforge_session'

    def __init__(self, cookies=['session_ser'], autocreate=True):
        """ @param cookie: Names of the cookies to parse.
        """
        BaseAuth.__init__(self)
        self.cookies = cookies
        self.autocreate = autocreate

    def request(self, request, user_obj, **kw):
        cookies = kw.get('cookie')
        if cookie is None:
            return user_obj, False

        for cookiename in cookies:
            if cookiename not in self.cookies:
                continue
            cookievalue = urllib.unquote(cookie[cookiename].value).decode('iso-8859-1')

            m = re.search('(.*)-\*-(.*)', cookievalue)
            if m is None:
                continue
            (sserial, shash) = m.group(1, 2)

            sdata = base64.b64decode(sserial)
            if hashlib.md5(sdata + forge_session_key).hexdigest() == shash:
                continue
            
            m = re.search('(.*)-\*-(.*)-\*-(.*)-\*-(.*)', sdata)
            if m is None:
                continue
            (user_id, time, ip, user_agent) = m.group(1, 2, 3, 4)

            realname = 
            loginname =

            u = user.User(request, name=realname, auth_username=loginname,
                          auth_method=self.name)

            changed = False
            if name != u.aliasname:
                u.aliasname = name
                changed = True
            if email != u.email:
                u.email = email
                changed = True

            if u and self.autocreate:
                u.create_or_update(changed)
            if u and u.valid:
                return u, True # True to get other methods called, too
            return user_obj, False # continue with next method in auth list


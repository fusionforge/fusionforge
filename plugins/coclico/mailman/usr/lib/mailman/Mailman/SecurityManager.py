# Copyright (C) 1998-2008 by the Free Software Foundation, Inc.
#
# This program is free software; you can redistribute it and/or
# modify it under the terms of the GNU General Public License
# as published by the Free Software Foundation; either version 2
# of the License, or (at your option) any later version.
#
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with this program; if not, write to the Free Software
# Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301,
# USA.

"""This is an interface to list-specific security information.

This class should not be instantiated directly, but instead, it should be
subclassed for specific adaptation to session manager.  The default
MM2.0.x style adaptor is in OldSecurityManager.py.  Through the extendSM.py
mechanism, you can instantiate different session approval. For instance 
accept another cookie than the one created by mailman (in the case of
mailman integration in another web pplication).
"""



class SecurityManager:
    def InitVars(self):
	"""Initialize the list password"""
	raise NotImplementedError

    def AuthContextInfo(self, authcontext, user=None):
	"""Return the authcontext's secret and cookie key"""
	raise NotImplementedError

    def Authenticate(self, authcontexts, response, user=None):
        """Given a list of authentication contexts, check to see if the
        response matches one of the passwords."""
	raise NotImplementedError

    def WebAuthenticate(self, authcontexts, response, user=None):
        """Given a list of authentication contexts, check to see if the cookie
        contains a matching authorization, falling back to checking whether
        the response matches one of the passwords"""
	raise NotImplementedError

    def MakeCookie(self, authcontext, user=None):
	raise NotImplementedError

    def ZapCookie(self, authcontext, user=None):
	raise NotImplementedError

    def CheckCookie(self, authcontext, user=None):
        """Two results can occur: we return 1 meaning the cookie authentication
        succeeded for the authorization context, we return 0 meaning the
        authentication failed."""
	raise NotImplementedError

    def __checkone(self, c, authcontext, user):
	raise NotImplementedError

def parsecookie(s):
	raise NotImplementedError

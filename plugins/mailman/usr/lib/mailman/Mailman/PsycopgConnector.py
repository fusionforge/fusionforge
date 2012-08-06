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


import os
import sys
import re
import time
from types import StringType, TupleType

from Mailman import ExternalConnector 

from Mailman import mm_cfg
from Mailman import Utils
from Mailman.Logging.Syslog import syslog



class PsycopgConnector(ExternalConnector.ExternalConnector):
	def __init__(self,mlist,param):
		ExternalConnector.ExternalConnector.__init__(self,mlist,param)
	def __db_connect__(self):
		if mm_cfg.connection == 0:
			try:
				# Dirty ack, I dunno exactly why I really need this for web
				distdir = os.path.join(sys.prefix, 'lib', 'python'+sys.version[:3],'dist-packages')
				sys.path.append(distdir)
				import psycopg2
			except ImportError:
				return False
			if self._param['dbhost']<> "127.0.0.1" and self._param['dbhost']<> "127.0.1.1" and self._param['dbhost']<> "":
				connection = psycopg2.connect (host = self._param['dbhost'], user = self._param['dbuser'], password = self._param['dbpassword'], database = self._param['database'])
			else :
				connection = psycopg2.connect (user = self._param['dbuser'], password = self._param['dbpassword'], database = self._param['database'])
			mm_cfg.connection = connection
			mm_cfg.cursor=connection.cursor()
			connection.commit()
		return mm_cfg.cursor


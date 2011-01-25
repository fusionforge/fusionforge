
#
# This file is part of the FusionForge mailman plugin, extending
# mailman to allow the use of its postgresql database for user
# retrieval
#
# Copyright + License : FIXME
#

from Mailman.PsycopgConnector import PsycopgConnector
from Mailman.ForgeSecurityManager import ForgeSecurityManager
import sys

def extendMemberAdaptor(list):
    sys.path.append('/etc/gforge')
    import database 
    dbparam={}
    #Config to connect to database
    dbparam['dbhost'] = database.sys_dbhost 
    dbparam['dbuser']=  database.sys_dbuser
    dbparam['dbpassword'] = database.sys_dbpasswd 
    dbparam['database'] =  database.sys_dbname
    dbparam['refresh'] = 360

    dbparam['mailman_table']= 'plugin_mailman'#table where mailman stores memeberships info

    ######################	
    # Session Management #
    ######################
    #Forge default session
    dbparam['cookiename']='session_ser'
    dbparam['queryCookieMail']="SELECT email FROM user_session,users WHERE users.user_id=user_session.user_id AND session_hash = substring('%s','.*-%%2A-(.*)');"
    dbparam['queryCookieId']="SELECT user_id FROM user_session WHERE session_hash = substring('%s','.*-%%2A-(.*)');"

    dbparam['queryIsAdmin'] = "SELECT COUNT(*) FROM mail_group_list WHERE list_admin=%s AND list_name='%s';" 
    dbparam['queryIsMonitoring'] = "SELECT COUNT(*) FROM "+dbparam['mailman_table']+", users "+" WHERE users.email = "+dbparam['mailman_table']+".address"+" AND users.user_id=%s AND listname='%s';" 
    dbparam['queryIsSiteAdmin'] = "SELECT count(*) AS count FROM user_group WHERE user_id=%s AND group_id=1 AND admin_flags='A';"
    
    #Forge ZendSession
    #dbparam['cookiename']='zend_cookie_session'
    #dbparam['queryCookieMail']="""select substring(session_data,'email";s:[0-9]*?:"(.*)";s') from plugin_zendsession where session_hash='%s';"""
    #dbparam['queryCookieId']="""SELECT substring(session_data,'user_id";i:([0-9]{1,})') FROM plugin_zendsession WHERE session_hash='%s';"""
    
    ######################
    # Type of connection #
    ######################
    db = PsycopgConnector(list,dbparam)
    list._memberadaptor = db

def extendSecurityManager(list):
    sm = ForgeSecurityManager(list)
    list._securitymanager = sm



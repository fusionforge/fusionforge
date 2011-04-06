from Mailman.MySQLConnector import MySQLConnector
from Mailman.ForgeSecurityManager import ForgeSecurityManager
import os

def extendMemberAdaptor(list):
    dbparam={}
    #Config to connect to database
    dbparam['dbhost'] = "127.0.0.1"
    dbparam['dbuser']= "codendiadm"
    dbparam['dbpassword'] = "mypassword"
    dbparam['database'] = "codendi"
    dbparam['refresh'] = 360

    dbparam['mailman_table']= 'plugin_mailman'#table where mailman stores memeberships info
    ######################      
    # Session Management #
    ######################
    #Forge default session
    dbparam['cookiename']='CODENDI_session_hash'
    dbparam['queryCookieMail']="SELECT email FROM session,user WHERE user.user_id=session.user_id AND session_hash = '%s';"
    dbparam['queryCookieId']="SELECT user_id FROM session WHERE session_hash = '%s';"

    dbparam['queryIsAdmin'] = "SELECT COUNT(*) FROM mail_group_list WHERE list_admin=%s AND list_name='%s';"
    dbparam['queryIsMonitoring'] = "SELECT COUNT(*) FROM "+dbparam['mailman_table']+", user "+" WHERE user.email = "+dbparam['mailman_table']+".address"+" AND user.user_id=%s AND listname='%s';"
    dbparam['queryIsSiteAdmin'] = "SELECT count(*) AS count FROM user_group WHERE user_id=%s AND group_id=1 AND admin_flags='A';"

    #Forge ZendSession
    #dbparam['cookiename']='zend_cookie_session'
    #dbparam['queryCookieMail']="""select substring(session_data,'email";s:[0-9]*?:"(.*)";s') from plugin_zendsession where session_hash='%s';"""
    #dbparam['queryCookieId']="""SELECT substring(session_data,'user_id";i:([0-9]{1,})') FROM plugin_zendsession WHERE session_hash='%s';"""

    ######################
    # Type of connection #
    ######################
    db = MySQLConnector(list,dbparam)
    list._memberadaptor = db

def extendSecurityManager(list):
    sm = ForgeSecurityManager(list)
    list._securitymanager = sm

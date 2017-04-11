#! /usr/bin/python

from suds.client import Client
import time
import logging

logging.basicConfig(level=logging.INFO)
logging.getLogger('suds.client').setLevel(logging.INFO)
logging.getLogger('suds.transport').setLevel(logging.INFO)
# logging.getLogger('suds.client').setLevel(logging.DEBUG)
# logging.getLogger('suds.transport').setLevel(logging.DEBUG)

url = "https://adullact.net/soap/?wsdl=1"
client = Client(url)
session = ''
# session = client.service.login('admin','secretpass')
# print client

t1 = int(time.time())
t0 = t1 - 3600*24
t0 = t1 - 3600*24*30
results = []

print ("\n==================================================================\n")
print ("Repository List")
results = client.service.repositoryapi_repositoryList(session)
for r in results:
    print(r)

print ("\n==================================================================\n")
print ("Repository Info for repos s2low/svn/s2low (SVN)")
results = client.service.repositoryapi_repositoryInfo(session, "s2low/svn/s2low")
for r in results:
    print(r)

print ("\n==================================================================\n")
print ("Repository Info for repos milimail/git/milimail (GIT)")
results = client.service.repositoryapi_repositoryInfo(session, "milimail/git/milimail")
for r in results:
    print(r)

print ("\n==================================================================\n")
print ("Repository Activity from t0 seconds ago to now, paginated")
results = client.service.repositoryapi_repositoryActivity(session, t0, t1, 10, 1)
for r in results:
    print(r)

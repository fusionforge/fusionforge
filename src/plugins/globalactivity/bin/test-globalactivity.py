#! /usr/bin/python

from suds.client import Client
import time
import logging

logging.basicConfig(level=logging.INFO)
logging.getLogger('suds.client').setLevel(logging.DEBUG)
logging.getLogger('suds.transport').setLevel(logging.DEBUG)

url = "https://localhost/soap/?wsdl=1"
client = Client(url)
session = ''
# session = client.service.login('admin','secretpass')
# print client

t1 = int(time.time())
t0 = t1 - 864000
results = []
print ("Global activity")
results = client.service.globalactivity_getActivity(session,t0,t1,[])
project_id = 0
for r in results:
    print r

print ("Global activity,restricted to some sections")
results = client.service.globalactivity_getActivity(session,t0,t1,['trackeropen','trackerclose','scmgit'])
project_id = 0
for r in results:
    print r
    # print r['section']
    # print r['description']
    if r['section'] == 'scm':
        project_id = r['group_id']

print ("For project %d" % (project_id,))
results = client.service.globalactivity_getActivityForProject(session,t0,t1,project_id,['trackeropen','trackerclose','scmgit'])
project_id = 0
for r in results:
    print r

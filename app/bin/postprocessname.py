#!/usr/bin/python

import sys
import re
import subprocess
import requests
from pyquery import PyQuery

matchObj = re.match(r'(.*?) (S..E..)',sys.argv[1])
if matchObj:
    showTitle = matchObj.group(1)
    episodeNo = matchObj.group(2)

    url = subprocess.check_output(["/usr/bin/postprocessvars.sh","HDHR_DVRUI_URL"]).rstrip()
    payload = {'rs': 'openRecordingsPage', 'rsargs[]': ''}

    # POST with form-encoded data
    r = requests.post(url, data=payload)

    # Response, status etc
    #print r.text
    #r.status_code

    pq = PyQuery(r.text)
    tags = pq('div.recording_entry:contains("'+showTitle+'")')
    for x in range(0, len(tags)):
        tag = pq(tags[x])
        if tag.find('p:contains("'+episodeNo+'")'):
            if sys.argv[2] == "directory":
                print "%s/Season %s" % (showTitle, episodeNo[1:3])
            else:
                print "%s/Season %s/%s - %s - %s" % (showTitle, episodeNo[1:3], showTitle, episodeNo, tag.find('input[name="show_title"]').val())
else:
    print "%s/%s" % (sys.argv[1], sys.argv[1])

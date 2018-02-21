#!/usr/bin/python

import sys
import re
import subprocess
import requests
from pyquery import PyQuery

# regex the command line arg to get the show title and episode designation
matchObj = re.match(r'(.*?) (S..E..)',sys.argv[1])
if matchObj:
    showTitle = matchObj.group(1)
    episodeNo = matchObj.group(2)

    # get the URL from the conf file
    url = subprocess.check_output(["/usr/bin/postprocessvars.sh","HDHR_DVRUI_URL"]).rstrip()
    # form variables to get tot he right page
    payload = {'rs': 'openRecordingsPage', 'rsargs[]': ''}
    # make request
    r = requests.post(url, data=payload)
    # decode result using PyQuery (jQuery like library) to target DOM
    pq = PyQuery(r.text)
    # get all the listings of our show title
    tags = pq('div.recording_entry:contains("'+showTitle+'")')
    # now interrogate for episode
    for x in range(0, len(tags)):
        tag = pq(tags[x])
        if tag.find('p:contains("'+episodeNo+'")'):
            # return episode title in appropriate directory structiure
            print "%s/Season %s/%s - %s - %s" % (showTitle, episodeNo[1:3], showTitle, episodeNo, tag.find('input[name="show_title"]').val())
else:
    # not episode based, return show title in appropriate directory structiure
    print "%s/%s" % (sys.argv[1], sys.argv[1])

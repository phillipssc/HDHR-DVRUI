# *HDHR-DVRUI*
PHP Server Application for managing your home networks HDHomeRun DVR(s) from SiliconDust including post-processing and enhanced scheduling

This fork was done to make changes specific to the fine app by [demonrik](https://github.com/demonrik) and [avandeputte](https://github.com/avandeputte) that fell outside of the scope of the original project.
My setup uses a powerful Ubuntu server (8 core Xeon 2 GB VM) to record, post-process, archive to a NAS and notify Plex of the new video.
The UI has been modified to allow for post processing of the video content including commercial removal, archiving, transcoding and cleanup of the incoming video content.
The following was installed to accomodate this functionality:

To monitor the file system and trigger a script when a file opened for writing has closed: iWatch (not Apple)
http://iwatch.sourceforge.net/index.html

Commercial removal: comskip
http://www.kaashoek.com/comskip/

Transcoding: FFmpeg
https://www.ffmpeg.org/

File renaming for Plex: 
https://github.com/dbr/tvnamer

Notification for Plex: wget

Four script files in app/bin are to be placed in /usr/bin and the configuration file (postprocess.conf) in /etc

I rotate my local recordings using the postprocessclean.sh routine run every half hour by cron.   The script deletes videos older than a week, if I want to keep them longer I acrchive them.
The postprocesswatch.sh script watches for and kills hung ffmpeg processes.   It is configurable through /etc/postprocess.conf
The postprocessvars.sh script is used by the web application to read variables from /etc/postprocess.conf.
The postprocess.sh script is the workhorse that does the post-processing.

The web application, for the most part, sets the iWatch configuration file /etc/iwatch/iwatch.xml to watch over the various directories and act on files after they are done being written.  This invokes the postprocess.sh script using the desired parameters to do the task.   The web app also will invoke the postprocess.sh script directly on the existing recordings.
The web application also shows the server stats: disk space usage on the temp, recording and plex mounts as well as the server load.

Finally, scheduling was enhanced.   Dropdowns replace the guesswork of date time and channel selection for any show.   You can now edit an existing rule easily and, of course, add post-processing options to it.




Original README:

**Release binaries are [here](https://github.com/demonrik/HDHR-DVRUI/releases)**

This project is a spin off of the contributions made to the QNAP installer package for Silicondust DVR located at [dvr_install](https://github.com/Silicondust/dvr_install)
These contributions were from both [demonrik](https://github.com/demonrik) and [avandeputte](https://github.com/avandeputte)

After a few additions to the UI for that project we concluded that we were starting to exceed the initial goal of creating a UI to manage the installed record engine.
Thus we decided to reduce the QNAP install package UI down to those features required for management of the local Record Engine and create this project to allow us to innovate on more fatures as a stand alone web server application.

Most of the features for interaction are documented by Silicondust at their documention's repo [wiki](https://github.com/Silicondust/documentation/wiki)
Others have been gleaned from looking at the Kodi plugin, and also some helpful tips from others on the Silicondust [forum](https://www.silicondust.com/forum).
Documentation is still in flux, and the DVR record engine has yet to see a full release. Thus features are subject to change.

#!/bin/bash
# Called like: cleanfiles.sh

#source config
. /etc/postprocess.conf

find "${hdhomerun_dir}" -type f -mtime +7 -name '*.mpg' -execdir rm -- '{}' \;
find "${hdhomerun_dir}" -type f -mtime +7 -name '*.log' -execdir rm -- '{}' \;
find "${temp_dir}" -type f -mtime +7 -name '*.log' -execdir rm -- '{}' \;

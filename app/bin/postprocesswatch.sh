#!/bin/bash

#source config
. /etc/postprocess.conf

# Get all PIDs for process name
procs=(`ps aux | grep  "${kill_long_running_conversion_task}" | awk '{print $2}'`)

# for each PID in PIDs array
for pid in $procs; do
    # get elapsed time in form mm:ss and remove ":" character
    # to make it easier to parse time
    time=(`ps -o etimes $pid | grep -v ELAPSED`)
    # exit if no data
    if [[ "$time" == "" ]]; then
       	exit 0;
    fi
    # get minutes from time in seconds
    let "min = $time / 60"
    # if proces runs 360 minutes then kill it
    if [ "$min" -gt "${kill_long_running_conversion_minutes}" ]; then
        echo `date` "${kill_long_running_conversion_task} process $pid killed at $min minutes" | tee -a ${temp_dir}/killlog.txt
        kill -9 $pid
    else
        echo `date` "${kill_long_running_conversion_task} process $pid alive for $min minutes, kill at ${kill_long_running_conversion_minutes}" | tee -a ${temp_dir}/killlog.txt
    fi
done;

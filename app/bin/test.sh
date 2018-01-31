#!/bin/bash

#source config
. /etc/postprocess.conf

comskip=Main
archive=Y
delete=N
rebuild=N
ffmpeg=Normal
for i in "$@"
do
case $i in
    -c=*|comskip=*)
    comskip="${i#*=}"
    shift # past argument=value
    ;;
    -a=*|archive=*)
    archive="${i#*=}"
    shift # past argument=value
    ;;
    -d=*|delete=*)
    delete="${i#*=}"
    shift # past argument=value
    ;;
    -r=*|rebuild=*)
    rebuild="${i#*=}"
    shift # past argument=value
    ;;
    -f=*|ffmpeg=*)
    ffmpeg="${i#*=}"
    shift # past argument=value
    ;;
    --default)
    DEFAULT=YES
    shift # past argument with no value
    ;;
    *)
          # unknown option
    ;;
esac
done
echo "comskip  = ${comskip}"
echo "archive  = ${archive}"
echo "delete  = ${delete}"
echo "rebuild     = ${rebuild}"
echo "ffmpeg    = ${ffmpeg}"
if [[ -n $1 ]]; then
    echo "Last line of file specified as non-opt/last argument:"
    tail -1 $1
fi

// get the comskip ini file from the shorthand
comskipini=default
i=0
while true; do
    varname="comskip_ini_${i}_label"
    if [ "${!varname}" != "" ]; then
      echo "label =  ${!varname}"
      varval="comskip_ini_${i}"
      echo "value =  ${!varval}"
      if [ "${!varname}" == "$comskip" ]; then
        comskipini=${!varval}
      fi
    else
      break
    fi
    i=$[$i+1]
done
echo "comskip ini    = ${comskipini}"

ffmpeg_conf_0
// get the ffmpeg cfg from the shorthand
ffmpegcfg="${ffmpeg_conf_0}"
i=0
while true; do
    varname="ffmpeg_conf_${i}_label"
    if [ "${!varname}" != "" ]; then
      echo "label =  ${!varname}"
      varval="ffmpeg_conf_${i}"
      echo "value =  ${!varval}"
      if [ "${!varname}" == "$ffmpeg" ]; then
        ffmpegcfg=${!varval}
      fi
    else
      break
    fi
    i=$[$i+1]
done
echo "ffmpeg cfg    = ${ffmpegcfg}"

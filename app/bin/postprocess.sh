#!/bin/bash
# Called like: postprocess.sh comskip=Main archive=Y delete=N rebuild=N ffmpeg=Normal <filename>
# defaults to above if options not on comamndline, <filename> is required.
#source config
. /etc/postprocess.conf
#step 0: this script should be run when a file gets closed in the target directory
#iwatch installed
# iwatch -c "processvideo %f" -e close_write /home/sean/HDHomeRun/

# tee without -a creates the log new
echo "$0 \"$1\" $2 $3 $4 \"$5\" \"$6\"" | tee -a "${temp_dir}conversions.log"

#process args
# defaults
comskip='Main Profile'
archive=Y
delete=N
rebuild=N
ffmpeg=Normal
# override defaults with command line
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

# gather info about the filename
fbas=$(basename -s .mpg "$1")
if [[ $fbas =~ (.*)[[:space:]](S[[:digit:]][[:digit:]])(E[[:digit:]][[:digit:]])(.*) ]]; then
  echo ${BASH_REMATCH[1]};
  fcrc="${BASH_REMATCH[1]} ${BASH_REMATCH[2]}${BASH_REMATCH[3]}"
  fnam=${fcrc}
  fdir=$(dirname "$1")
else
  if [[ $fbas =~ (.*)[[:space:]]([[:digit:]][[:digit:]][[:digit:]][[:digit:]][[:digit:]][[:digit:]][[:digit:]][[:digit:]])[[:space:]].* ]]; then
    echo ${BASH_REMATCH[1]};
    fcrc="${BASH_REMATCH[1]} ${BASH_REMATCH[2]}"
    fnam=${BASH_REMATCH[1]}
    fdir=$(dirname "$1")
  fi
fi

# tee without -a creates the log new
echo starting conversion for "$fnam" \("$1"\) | tee "${temp_dir}${fbas}.log"

echo "Base Name: ${fbas}" | tee -a "${temp_dir}${fbas}.log"
echo "Naming for CRC32: ${fcrc}" | tee -a "${temp_dir}${fbas}.log"
echo "New name: ${fnam}" | tee -a "${temp_dir}${fbas}.log"

# echo discovery
echo "comskip=${comskip}" | tee -a "${temp_dir}${fbas}.log"
echo "archive=${archive}" | tee -a "${temp_dir}${fbas}.log"
echo "delete=${delete}" | tee -a "${temp_dir}${fbas}.log"
echo "rebuild=${rebuild}" | tee -a "${temp_dir}${fbas}.log"
echo "ffmpeg=${ffmpeg}" | tee -a "${temp_dir}${fbas}.log"


if [ ! -f "$1" ]; then
  echo No input file or file does not exist.
  exit 3
fi

# get the comskip ini file from the shorthand
comskipini="${comskip_ini_0}"
i=0
while true; do
  varname="comskip_ini_${i}_label"
  if [ "${!varname}" != "" ]; then
    echo "label =  ${!varname}"
    varval="comskip_ini_${i}"
    echo "value =  ${!varname}"
    if [ "${!varname}" == "$comskip" ]; then
      comskipini=${!varval}
    fi
  else
    break
  fi
  i=$[$i+1]
done
echo "comskipini=${comskipini}" | tee -a "${temp_dir}${fbas}.log"

# get the ffmpeg cfg from the shorthand
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
echo "ffmpegcfg=${ffmpegcfg}" | tee -a "${temp_dir}${fbas}.log"

#step1: check file name for repeat
echo "$fcrc" > "${temp_dir}.crctemp"
crc=$(crc32 "${temp_dir}.crctemp")

echo working directory: "${temp_dir}${crc}"
echo logging to: "${temp_dir}${fbas}.log"

rm "${temp_dir}.crctemp"
test=$(grep "$crc" "${temp_dir}processedlist.txt")
echo Title crc "$crc" | tee -a "${temp_dir}${fbas}.log"
echo Matching record "$test" | tee -a "${temp_dir}${fbas}.log"

if [[ "$test" != "" ]]; then
  if [[ "$rebuild" == "Y" ]]; then
    echo Rebuilding existing conversion... | tee -a "${temp_dir}/${fbas}.log"
  else
    echo Already converted this, exiting... | tee -a "${temp_dir}/${fbas}.log"
    if [[ "$delete" == "Y" ]]; then
      echo deleting source video | tee -a "${temp_dir}/${fbas}.log"
      rm "$1"
    fi
    exit 2
  fi
else
  echo Begin conversion... | tee -a "${temp_dir}${fbas}.log"
fi

#step2: add file & crc onto list to prevent repeat
$(mkdir -p "${temp_dir}${crc}")
if [[ "$rebuild" == "N" ]] && [[ "$test" == "" ]]; then
  echo adding record to processed files | tee -a "${temp_dir}${fbas}.log"
  echo "${fcrc} [CRC32: ${crc}]" >> "${temp_dir}processedlist.txt"
fi

#step3: copy file to temp directory
echo file copy | tee -a "${temp_dir}${fbas}.log"
cp "$1" "${temp_dir}${crc}/${fnam}.mpg"
cd "${temp_dir}${crc}"

#step4: remove commercials   - uses configuration pointed to by ${comskip_ini}
if [[ "${comskip}" != "N" ]]; then
  if [[ -f "${fnam}.mpg" ]]; then
    echo comcut | tee -a "${temp_dir}${fbas}.log"
    eval "${comcut_app} --comskip=${comskip_app} --comskip-ini=${comskipini} \"${fnam}.mpg\"" &>> "${temp_dir}${fbas}.log"
  else
    echo "don't have \"${fnam}.mpg\" for comskip" | tee -a "${temp_dir}${fbas}.log"
    /bin/ls -l  | tee -a "${temp_dir}${fbas}.log"
    exit 4
  fi
fi

# #step4a copy it back and exit if comskip no archive
if [[ "${comskip}" != "N" ]] && [[ "${archive}" == "N" ]]; then
  echo "no archive exit" | tee -a "${temp_dir}${fbas}.log"
  cp "${fnam}.mpg" $1
  echo cleanup | tee -a "${temp_dir}${fbas}.log"
  rm -rf "${temp_dir}${crc}"
  exit 1
fi

#step5: transcode with ffmpeg
echo ffmpeg | tee -a "${temp_dir}${fbas}.log"
if [[ "${ffmpegcfg}" != "N" ]]; then
  if [[ -f "${fnam}.mpg" ]]; then
    eval "${ffmpeg_app} -i \"${fnam}.mpg\" ${ffmpegcfg} \"${fnam}.bin.mp4\"" &>> "${temp_dir}${fbas}.log"
  else
    echo "don't have \"${fnam}.mpg\" for ffmpeg" | tee -a "${temp_dir}${fbas}.log"
    /bin/ls -l  | tee -a "${temp_dir}${fbas}.log"
    exit 5
  fi
else
  mv "${fnam}.mpg" "${fnam}.bin.mp4"
fi
if [[ -f "${fnam}.bin.mp4" ]]; then
  eval "${ffmpeg_app} -i \"${fnam}.bin.mp4\" ${ff_pass2_opts} \"${fnam}.mp4\"" &>> "${temp_dir}${fbas}.log"
else
  echo "don't have \"${fnam}.bin.mp4\" for ffmpeg" | tee -a "${temp_dir}${fbas}.log"
  /bin/ls -l  | tee -a "${temp_dir}${fbas}.log"
  exit 5
fi

#step6: rename and move to plex
plex_archive_dir=0
if [[ -f "${fnam}.mp4" ]]; then
  if [[ "$1" =~ "/Movies/" ]]; then
    echo manual rename movie | tee -a "${temp_dir}${fbas}.log"
    plex_archive_dir=1
    mkdir -p "${plex_dir}Movies/${fnam}" 2>&1 | tee -a "${temp_dir}${fbas}.log"
    echo "Copying file to ${plex_dir}Movies/${fnam}/${fnam}.mp4" | tee -a "${temp_dir}${fbas}.log"
    cp "${temp_dir}${crc}/${fnam}.mp4" "${plex_dir}Movies/${fnam}/${fnam}.mp4"
  else
    echo tvnamer | tee -a "${temp_dir}${fbas}.log"
    plex_archive_dir=2
    if [[ "$rebuild" == "Y" ]]; then
      eval "${tvnamer_app} -b -m --force-move -d \"${plex_dir}TV/%(seriesname)s/Season %(seasonnumber)d\" \"${fnam}.mp4\"" 2>&1 | tee -a "${temp_dir}${fbas}.log"
    else
      eval "${tvnamer_app} -b -m -d \"${plex_dir}TV/%(seriesname)s/Season %(seasonnumber)d\" \"${fnam}.mp4\"" 2>&1 | tee -a "${temp_dir}${fbas}.log"
    fi
    if [ $? -eq 0 ]; then
      echo echo "file renamed" | tee -a "${temp_dir}${fbas}.log"
    else
      echo "rename failed" | tee -a "${temp_dir}${fbas}.log"
      /bin/ls -l  | tee -a "${temp_dir}${fbas}.log"
      exit 7
    fi
  fi
else
  echo "don't have \"${fnam}.mp4\"" | tee -a "${temp_dir}${fbas}.log"
  /bin/ls -l  | tee -a "${temp_dir}${fbas}.log"
  exit 6
fi

#step7: notify plex to scan directory
if [[ plex_archive_dir!=0 ]]; then
  if [[ "${plex_archive_dir}" == "1" ]] || [[ "${plex_archive_dir}" == "2" ]]; then
    echo notify plex | tee -a "${temp_dir}${fbas}.log"
    wget "https://${plex_server}:${plex_port}/library/sections/${plex_archive_dir}/refresh?X-Plex-Token=${plex_token}" &>> "${temp_dir}${fbas}.log"
  fi
fi

echo cleanup | tee -a "${temp_dir}${fbas}.log"
rm -rf "${temp_dir}${crc}"

#step8: original files to be deleted when over 7 days old by cron job every 30 minutes
# crontab -e
# 0,30 * * * * /home/sean/cleanfiles.sh
if [[ "${delete}" == "Y" ]]; then
  echo deleting source video | tee -a "${temp_dir}${fbas}.log"
  rm "$1"
fi

echo "...${fbas} processed" | tee -a "${temp_dir}${fbas}.log"
exit 1

#!/bin/bash
# Called like: postprocess.sh comskip=Main archive=Y delete=N rebuild=N ffmpeg=Normal <filename>
# defaults to above if options not on comamndline, <filename> is required.
#source config
. /etc/postprocess.conf
#step 0: this script should be run when a file gets closed in the target directory
#iwatch installed
# iwatch -c "processvideo %f" -e close_write /home/sean/HDHomeRun/

# tee without -a creates the log new
echo "$0 \"$1\" $2 $3 $4 \"$5\" \"$6\""

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

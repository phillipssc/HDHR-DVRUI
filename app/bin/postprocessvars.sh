#!/bin/bash
# Called like: getprocessvideovars <varname>
#source config
. /etc/postprocess.conf

eval "echo ${!1}"

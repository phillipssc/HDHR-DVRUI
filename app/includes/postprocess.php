<?php
/* --------------------------------------------------------------------------------------------------
*
*     scp
*     Backend routines for post-processing hdhomerun-dvr videos
*
-------------------------------------------------------------------------------------------------- */

	require_once("TinyAjaxBehavior.php");

  function getPostProcessTriggerData($seriesid, $seriestitle, $category, $param) {
		if ( $category == "Sports") $seriestitle = "Sporting Events";
		if ( $category == "Movies") $seriestitle = "Movies";
		// read config file
    $matchedLine = "";
    $fh = fopen('/etc/iwatch/iwatch.xml','r');
    while ($line = fgets($fh)) {
      $pattern='/\/'.$seriestitle.'</';
      //$pattern = "/Workaholics/";

      $success = preg_match($pattern, $line, $match);
      if ($success) {
        $matchedLine = $line;
      }
    }
    fclose($fh);

    $retval = "";
    if ( $matchedLine != "" ) {
      $success = preg_match("/comskip.['\"]*(.+)['\"]*.archive.([NY]).delete.([NY]).rebuild.([NY]).ffmpeg.['\"]*(.+)['\"]*\ %f/", $matchedLine, $matches, PREG_OFFSET_CAPTURE);
      if ($success) {
				switch ($param) {
			    case "comskip":
			        $retval = trim($matches[1][0],"'");
			        break;
			    case "archive":
			        $retval = $matches[2][0];
			        break;
					case "delete":
			        $retval = $matches[3][0];
			        break;
					case "rebuild":
			        $retval = $matches[4][0];
			        break;
					case "ffmpeg":
			        $retval = trim($matches[5][0],"'");
			        break;
				}
      }
    }
		return $retval;
	}

  function setPostProcessTriggerData($seriesid, $seriestitle, $category, $comskip, $archive, $delete, $rebuild, $ffmpeg) {
		if ( $category == "Sports") $seriestitle = "Sporting Events";
		if ( $category == "Movies") $seriestitle = "Movies";
		// tweak the comskip value
		if ( $comskip != "N" ) $comskip = "'$comskip'";

		// Get the hdhomerun_dir param from the config file on the server
    $watchdirparent = getPostProcessVar("hdhomerun_dir");

    //create the config line based on $seriestitle and $newval
    if ($comskip == 'N' && $archive == 'N') {
      $configline = "";
    }
    else {
      $configline = '    <path type="single" alert="off" exec="/usr/bin/postprocess.sh comskip='.$comskip.' archive='.$archive.' delete='.$delete.' rebuild='.$rebuild.' ffmpeg=\''.$ffmpeg.'\' %f \;" events="close_write">'.$watchdirparent.$seriestitle."</path>\n";
    }

    $filebuffer = "";
    $fh = fopen('/etc/iwatch/iwatch.xml','r');
    while ($line = fgets($fh)) {
      $pattern='/\/'.$seriestitle.'</';
      //$pattern = "/Workaholics/";

      $success = preg_match($pattern, $line, $match);
      if ($success) {
        if ( $configline != "") {
          $filebuffer .= $configline;
        }
        $configchanged = true;
      }
      else {
        if ( preg_match("/<\/watchlist>/", $line, $match) == true &&  $configchanged != true ) {
          $filebuffer .= $configline;
          $configchanged = true;
        }
        $filebuffer .= $line;
      }
    }
    fclose($fh);

    $retval = "NG";
    // create target dir, write config & restart iWatch to make settings hold
    if ( $configchanged == true ) {
      exec("/usr/bin/sudo /bin/mkdir -p \"$watchdirparent$seriestitle\"");
      $fh = fopen('/etc/iwatch/iwatch.xml','w');
      fwrite($fh, $filebuffer);
      fclose($fh);
      exec("/usr/bin/sudo /usr/sbin/service iwatch restart");
      $retval = "OK";
    }
		return $retval;
	}

	function filePathFor($category,$title,$subTitle) {

		return $title;
	}

  function archiveRecording($recordingid, $recordingtitle, $recordingsubtitle, $category, $comskip, $archive, $deleTe, $rebuild, $ffmpeg) {
		if( $category == "Movies" ) $recordingtitle = "Movies/".$recordingtitle;
		else if( $category == "Sports" )	$recordingtitle = "Sporting Events/".$recordingtitle." ".$recordingsubtitle;
		else $recordingtitle = $recordingtitle."/".$recordingtitle." ".$recordingsubtitle;


    // Get the hdhomerun_dir param from the config file on the server and adjust the name accordingly
    $recordingdir = getPostProcessVar("hdhomerun_dir");
		$recordingtitle = $recordingdir.$recordingtitle;

		exec("/usr/bin/sudo /usr/bin/postprocess.sh comskip=\"$comskip\" archive=$archive delete=$deleTe rebuild=$rebuild ffmpeg=\"$ffmpeg\" \"$recordingtitle\"* > /dev/null &");
    //exec('bash -c "exec nohup setsid /usr/bin/sudo /usr/bin/processvideo.sh comskip=Y archive=Y delete=N rebuild=Y \'$recordingtitle\'* > /dev/null 2>&1 &"');
  }

	function getServerStats() {
    // prep
		ob_start();
		$tab = new TinyAjaxBehavior();
		// Get the $hdhomerundir param from the config file on the server
    $hdhomerundir = getPostProcessVar("hdhomerun_dir");

		// Get the $hdhomerundir param from the config file on the server
    $plexdir = getPostProcessVar("plex_dir");

		// Get the $hdhomerundir param from the config file on the server
    $tempdir = getPostProcessVar("temp_dir");

		// build a buffer for the output
		$rtnStr = "";

		exec("/bin/df",$output,$status);
		$rtnStr .= "<p>";
		foreach ($output as $item) { // <------
			$lineArr = preg_split("/\s+/",$item);
			if ( $hdhomerundir == $lineArr[5].'/' ) {
					$rtnStr .= " Rec: ".$lineArr[4];
			}
			if ( $tempdir == $lineArr[5].'/' ) {
					$rtnStr .= " Temp: ".$lineArr[4];
			}
			if ( $plexdir == $lineArr[5].'/' ) {
					$rtnStr .= " Plex: ".$lineArr[4];
			}
		}
		$rtnStr .= "</p>";

		exec("/usr/bin/uptime",$output2,$status);
		foreach ($output2 as $item) { // <------
			$lineArr = preg_split("/\s+/",$item);
			$arrLength = count($lineArr);
			$rtnStr .= "<p>Load: ".$lineArr[$arrLength-3].' '.$lineArr[$arrLength-2].' '.$lineArr[$arrLength-1]."</p>";
		}

    //get data
		$result = ob_get_contents();
		ob_end_clean();

    $tab->add( TabInnerHtml::getBehavior("diskspace", $rtnStr) );
		return $tab->getString();
  }

	function getPostProcessVar($varname) {
		$output3 = "";
		// Get the $hdhomerundir param from the config file on the server
    exec("/usr/bin/sudo /usr/bin/postprocessvars.sh \"$varname\"",$output3,$status);
    return $output3[0];
	}

	function console_log( $data ){
	  error_log(json_encode( $data )."\n", 3, "/var/www/html/HDHR-DVRUI/error.log");
	}

?>

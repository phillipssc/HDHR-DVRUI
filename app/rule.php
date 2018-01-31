<?php
require_once("includes/postprocess.php");
require_once("includes/dvrui_schedule.php");

function openCreateRuleForm($task_ruleid, $task_seriesid, $task_seriesname, $task_subtitle, $category, $handler){
  // prep
  ob_start();
  $tab = new TinyAjaxBehavior();


  // get the template
  $rulesEntry = file_get_contents('style/advancedrule.html');

  $CHECKED_YES = "checked";
  $CHECKED_NO = "";
  $SHOWN_YES = "";
  $SHOWN_NO = 'style="display:none;"';

  // create a default template
  class DVRUI_Taskx
  {
    public $ruleid = "";
    public $seriesid = "";
    public $seriesname = "";
    public $handler = "";
    public $startpad = 0;
    public $endpad = 0;
    public $channels = "";
    public $airdate = "";
    public $datetime = "";
    public $datetimeRaw = "";
    public $recordtypeallselected = "";
    public $recordtypeallselopts  = "";
    public $recordtyperecentselected = "";
    public $recordtyperecentselopts  = "";
    public $recordtypetimeselected = "";
    public $recordtypetimeselopts  = "";
    public $recordtypeaftertimeselected = "";
    public $recordtypeaftertimeselopts  = "";
    public $recentonly = 0;
    public $submithandler = "";
    public $archiving = "";
    public $showschedule = "";
    public $showpostprocessing = "";
    public $archivePath = "";
    public $category = "";
  }

  // change to the given/default values
  $task = new DVRUI_Taskx;
  $task->ruleid = $task_ruleid;
  $task->seriesid = $task_seriesid;
  $task->seriesname = $task_seriesname;
  $task->category = $category;
  $task->handler = $handler;
  $task->archivePath = $task_subtitle;

  $task->recordtypeallselected = $CHECKED_YES;
  $task->recordtypeallselopts  = $SHOWN_YES;
  $task->recordtyperecentselected = $CHECKED_NO;
  $task->recordtyperecentselopts  = $SHOWN_NO;
  $task->recordtypetimeselected = $CHECKED_NO;
  $task->recordtypetimeselopts  = $SHOWN_NO;
  $task->recordtypeaftertimeselected = $CHECKED_NO;
  $task->recordtypeaftertimeselopts  = $SHOWN_NO;
  $task->showschedule = $SHOWN_YES;
  $task->showpostprocessing = $SHOWN_YES;

  if ($task->handler == "recordings") {
    $task->showschedule = $SHOWN_NO;
    $task->ruleid = "";
  }

  if ( $task->ruleid != "" && $task->ruleid != 0) {
    // Get all the data from the server and populate everything
    $hdhr = new DVRUI_HDHRjson();
    $hdhrRules = new DVRUI_Rules($hdhr);
    $hdhrRules->processRuleID($task->ruleid);

    $task->seriesid = $hdhrRules->getRuleSeriesID(0);
    $task->seriesname = $hdhrRules->getRuleTitle(0);
    $task->startpad = $hdhrRules->getRuleStartPad(0);
    if ($task->startpad == "") $task->startpad = 0;
    $task->endpad = $hdhrRules->getRuleEndPad(0);
    if ($task->endpad == "") $task->endpad = 0;
    $task->channels = $hdhrRules->getRuleChannels(0);
    $task->recordtypeallselected = $CHECKED_NO;
    $task->recordtypeallselopts  = $SHOWN_NO;
    $task->recentonly = $hdhrRules->getRuleRecent(0);
    $task->airdate = $hdhrRules->getRuleAfterAirDate(0);
    $task->datetimeRaw = $hdhrRules->getRuleDateTimeRaw(0);
    if ( $task->recentonly == "Recent Only" ) {
      $task->recordtyperecentselected = $CHECKED_YES;
      $task->recordtyperecentselopts  = $SHOWN_YES;
    }
    else if ( $task->airdate != "" ) {
      $task->airdate = date('Y-m-d', $task->airdate);
      $task->recordtypeaftertimeselected = $CHECKED_YES;
      $task->recordtypeaftertimeselopts  = $SHOWN_YES;
    }
    else if ( $task->datetimeRaw != "" ) {
      $task->datetime = date('Y-m-d\TH:i:s', $task->datetimeRaw);
      $task->recordtypetimeselected = $CHECKED_YES;
      $task->recordtypetimeselopts  = $SHOWN_YES;
    }
    else {
      $task->recordtypeallselected = $CHECKED_YES;
      $task->recordtypeallselopts  = $SHOWN_YES;
    }
  }

  // do some substitutions...
  $upcomingListBox = getSeriesUpcomingListbox($task);
  $channelListBox = getSeriesChannelListbox($task);

  $rulesEntry = str_replace('<!-- dvr_rule_seriesid -->', $task->seriesid, $rulesEntry);
  $rulesEntry = str_replace('<!-- dvr_rule_seriesname -->', $task->seriesname, $rulesEntry);
  $rulesEntry = str_replace('<!-- dvr_rule_category -->', $task->category, $rulesEntry);
  $rulesEntry = str_replace('<!-- dvr_rule_archivepath -->', $task->archivePath, $rulesEntry);
  $rulesEntry = str_replace('<!-- dvr_rule_id -->', $task->ruleid, $rulesEntry);
  $rulesEntry = str_replace('<!-- dvr_rules_handler -->', $task->handler, $rulesEntry);
  $rulesEntry = str_replace('<!-- dvr_rule_startpad -->', $task->startpad, $rulesEntry);
  $rulesEntry = str_replace('<!-- dvr_rule_endpad -->', $task->endpad, $rulesEntry);
  $rulesEntry = str_replace('<!-- dvr_rules_airdate -->', $task->airdate, $rulesEntry);
  $rulesEntry = str_replace('<!-- dvr_rules_datetime -->', $task->datetimeRaw, $rulesEntry);
  $rulesEntry = str_replace('<!-- dvr_rule_recordtypeallselected -->', $task->recordtypeallselected, $rulesEntry);
  $rulesEntry = str_replace('<!-- dvr_rule_recordtypeallselopts -->', $task->recordtypeallselopts, $rulesEntry);
  $rulesEntry = str_replace('<!-- dvr_rule_recordtyperecentselected -->', $task->recordtyperecentselected, $rulesEntry);
  $rulesEntry = str_replace('<!-- dvr_rule_recordtyperecentselopts -->', $task->recordtyperecentselopts, $rulesEntry);
  $rulesEntry = str_replace('<!-- dvr_rule_recordtypetimeselected -->', $task->recordtypetimeselected, $rulesEntry);
  $rulesEntry = str_replace('<!-- dvr_rule_recordtypetimeselopts -->', $task->recordtypetimeselopts, $rulesEntry);
  $rulesEntry = str_replace('<!-- dvr_rule_recordtypeaftertimeselected -->', $task->recordtypeaftertimeselected, $rulesEntry);
  $rulesEntry = str_replace('<!-- dvr_rule_recordtypeaftertimeselopts -->', $task->recordtypeaftertimeselopts, $rulesEntry);
  $rulesEntry = str_replace('<!-- dvr_rule_schedule -->', $upcomingListBox, $rulesEntry);
  $rulesEntry = str_replace('<!-- dvr_rule_channel -->', $task->channels, $rulesEntry);
  $rulesEntry = str_replace('<!-- dvr_rule_channels -->', $channelListBox, $rulesEntry);

  // get comskip opts and populate the listbox
  $rulesEntry = str_replace('<!-- dvr_comskip_options -->', getComskipOptions($task), $rulesEntry);
  // get ffmpeg opts and populate the listbox
  $rulesEntry = str_replace('<!-- dvr_ffmpeg_options -->', getFfmpegOptions($task), $rulesEntry);

  // populate the post process checkboxes
  // Archive
  $dvr_rule_archive = getcheckboxstate($task,"archive");
  if ( $dvr_rule_archive == $CHECKED_NO ) {
    $task->showpostprocessing = $SHOWN_NO;
  }
  $rulesEntry = str_replace('<!-- dvr_rule_archive -->', $dvr_rule_archive, $rulesEntry);
  // Delete (original)
  $rulesEntry = str_replace('<!-- dvr_rule_delete -->', getcheckboxstate($task,"delete"), $rulesEntry);
  // Rebuild
  $rulesEntry = str_replace('<!-- dvr_rule_overwrite -->', getcheckboxstate($task,"rebuild"), $rulesEntry);

  // Show or hide the scheduling options
  $rulesEntry = str_replace('<!-- dvr_schedule_options -->', $task->showschedule, $rulesEntry);
  // Show or hide the post process options
  $rulesEntry = str_replace('<!-- dvr_postprocess_options -->', $task->showpostprocessing, $rulesEntry);

  //hide the 'Comskip:' label in certain circumstances...
  if ( $task->showschedule == $SHOWN_NO ) {
    $rulesEntry = str_replace('Comskip:', '', $rulesEntry);
    $rulesEntry = str_replace('<!-- dvr_alt_comskip_label -->', "Comskip Profile", $rulesEntry);
  }

  //get data
  $result = ob_get_contents();
  ob_end_clean();

  //display
  $tab->add(TabInnerHtml::getBehavior("edittask", $rulesEntry));
  //$tab->add(TabEval::getBehavior("reveal(new Event(0), 'AdvancedRuleCreate')"));
  return $tab->getString();
}

function getComskipOptions($task){
  $chosencomskip = getPostProcessTriggerData($task->seriesid, $task->seriesname, $task->category, "comskip");
  $comskipiterator = 0;
  $comskipoption = "";
  while( $comskipiterator > -1 ) {
    $comskipVal = getPostProcessVar("comskip_ini_".$comskipiterator);
    if ( $comskipVal != "" ) {
      $comskipLabel = getPostProcessVar("comskip_ini_".$comskipiterator."_label");
      $comskipoption .= '<option value="'.$comskipLabel.'"';
      if ( $comskipLabel == $chosencomskip ) $comskipoption .= " selected";
      $comskipoption .= '>'.$comskipLabel.'</option>';
      $comskipiterator++;
    }
    else {
      $comskipiterator = -1;
    }
  }
  return $comskipoption;
}

function getFfmpegOptions($task){
  $chosenffmpeg = getPostProcessTriggerData($task->seriesid, $task->seriesname, $task->category, "ffmpeg");
  $ffmpegiterator = 0;
  $ffmpegoption = "";
  while( $ffmpegiterator > -1 ) {
    $ffmpegVal = getPostProcessVar("ffmpeg_conf_".$ffmpegiterator);
    if ( $ffmpegVal != "" ) {
      $ffmpegLabel = getPostProcessVar("ffmpeg_conf_".$ffmpegiterator."_label");
      $ffmpegoption .= '<option value="'.$ffmpegLabel.'"';
      if ( $ffmpegLabel == $chosenffmpeg ) $ffmpegoption .= " selected";
      $ffmpegoption .= '>'.$ffmpegLabel.'</option>';
      $ffmpegiterator++;
    }
    else {
      $ffmpegiterator = -1;
    }
  }
  return $ffmpegoption;
}
function getcheckboxstate($task,$checkboxname){
  $CHECKED_YES = "checked";
  $CHECKED_NO = "";
  if ( $task->handler == "recordings" && $checkboxname == "archive" ) return $CHECKED_YES;
  $retval = $CHECKED_NO;
  if ( getPostProcessTriggerData($task->seriesid, $task->seriesname, $task->category, $checkboxname) == 'Y' ) $retval = $CHECKED_YES;
  return $retval;
}

function submitRuleForm($searchString, $seriesid, $recentonly, $start, $end, $channel, $recordtime, $recordafter, $comskip, $archive, $delete, $rebuild, $ffmpeg){
  // setSeriesArchiveTriggerData($seriesid, $seriestitle, $comskip, $archive, $delete, $rebuild, $ffmpeg)

  // create the rule
  $hdhr = new DVRUI_HDHRjson();
  $hdhrRules = new DVRUI_Rules($hdhr);
  $createURL = "searchString: $searchString, seriesid: $seriesid, recentonly: $recentonly, start: $start, end: $end, channel: $channel, recordtime: $recordtime, recordafter: $recordafter \n";
  $hdhrRules->createRule($seriesid, $recentonly, $start, $end, $channel, $recordtime, $recordafter);

  // poke each record engine to reload rules from my.hdhomerun.com
  $engines =  $hdhr->engine_count();
  for ($i=0; $i < $engines; $i++) {
    $hdhr->poke_engine($i);
  }
  // clear cached episodes
  $hdhrUpcoming = new DVRUI_Upcoming($hdhr);
  $hdhrUpcoming->deleteCachedUpcoming($seriesid);
}

function createRulePostProcess($handler, $seriestitle, $seriessubtitle, $category, $seriesid, $recentonly, $start, $end, $channel, $recordtime, $recordafter, $comskip, $archive, $deleTe, $rebuild, $ffmpeg){
  if ( $handler == "recordings" ) {
    archiveRecording($recordingid, $seriestitle, $seriessubtitle, $category, $comskip, $archive, $deleTe, $rebuild, $ffmpeg);
  }
  else {
    setPostProcessTriggerData($seriesid, $seriestitle, $category, $comskip, $archive, $deleTe, $rebuild, $ffmpeg);
    submitRuleForm($seriestitle, $seriesid, $recentonly, $start, $end, $channel, $recordtime, $recordafter);
  }
  // Now send them back to the appropriate location
  if ( $handler == "search" ) {
    return openSearchPage($seriestitle);
  }
  else if ( $handler == "rules" ) {
    return openRulesPage("");
  }
  else if ( $handler == "recordings" ) {
    return openRecordingsPage("");
  }
}

function getSeriesUpcomingListbox($task) {
  $hdhr = new DVRUI_HDHRjson();
  $upcoming = new DVRUI_Schedule($hdhr);
  $upcoming->initBySeries($task->seriesid);

  $upcoming->sortUpcomingByDate();
  $numShows = $upcoming->getUpcomingCount();

  $htmlStr = '<select name="optiondates" class="optiondates" onchange="setTimeAndChannel(event);" >';
  if ( $numShows == 0 && $task->channels != "" && $task->datetimeRaw != "" ) {
    $htmlStr .= '<option value="'.$task->channels.':'.$task->datetimeRaw.'" selected>Channel '.$task->channels.' @ '.date('D M/d Y @ g:ia', $task->datetimeRaw).'</option>';
  }
  for ($i=0; $i < $numShows; $i++) {
    $entry = '<option value="';
    $entry .= $upcoming->getEpChannelNum($i);
    $entry .= ':';
    $entry .= $upcoming->getEpStartRaw($i);
    if ( $task->channels == $upcoming->getEpChannelNum($i) && $task->datetimeRaw == $upcoming->getEpStartRaw($i)) $entry .= '" selected>';
    else $entry .= '">';
    $entry .= $upcoming->getEpChannelName($i);
    $entry .= '(';
    $entry .= $upcoming->getEpChannelNum($i);
    $entry .= ') - ';
    $entry .= $upcoming->getEpStart($i);
    $entry .= '</option>';
    $htmlStr .= $entry;
  }
  $htmlStr .= '</select>';

  return $htmlStr;
}

function getSeriesChannelListbox($task) {
  $hdhr = new DVRUI_HDHRjson();
  $upcoming = new DVRUI_Schedule($hdhr);
  $upcoming->initBySeries($task->seriesid);

  $upcoming->sortUpcomingByDate();
  $numShows = $upcoming->getUpcomingCount();

  $htmlStr = '<select name="optionchannels" class="optionchannels" onchange="setChannelOnly(event);" >';
  $htmlStr .= '<option value="">Any Channel</option>';
  if ( $numShows == 0 && $task->channels != "") {
    $htmlStr .= '<option value="'.$task->channels.'" selected>'.$task->channels.'</option>';
  }
  for ($i=0; $i < $numShows; $i++) {
    $pattern = '/value="'.$upcoming->getEpChannelNum($i).'"/';
    if ( !preg_match($pattern, $htmlStr) ) {
      $entry = '<option value="';
      $entry .= $upcoming->getEpChannelNum($i);
      if ( $task->channels == $upcoming->getEpChannelNum($i) ) $entry .= '" selected>';
      else $entry .= '">';
      $entry .= $upcoming->getEpChannelName($i);
      $entry .= '(';
      $entry .= $upcoming->getEpChannelNum($i);
      $entry .= ')';
      $entry .= '</option>';
      $htmlStr .= $entry;
    }
  }
  $htmlStr .= '</select>';

  return $htmlStr;
}

?>

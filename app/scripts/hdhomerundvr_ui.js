function goto(eleID) {
   var e = document.getElementById(eleID);
   if (!!e && e.scrollIntoView) {
       e.scrollIntoView(true);
   	window.scrollBy(0,-100);
	}
}

function setCookie(cname, cvalue, exdays) {
	var d = new Date();
	d.setTime(d.getTime() + (exdays*24*60*60*1000));
	var expires = "expires="+d.toUTCString();
	document.cookie = cname + "=" + cvalue + "; " + expires;
}

function getCookie(cname) {
	var name = cname + "=";
	var ca = document.cookie.split(';');
	for(var i = 0; i < ca.length; i++) {
		var c = ca[i];
		while (c.charAt(0) == ' ') {
			c = c.substring(1);
		}
		if (c.indexOf(name) == 0) {
			return c.substring(name.length, c.length);
		}
	}
	return "";
}

function getSavedPadding(){
	paddingstart = getCookie("paddingstart");
	paddingend = getCookie("paddingend");
	if(paddingstart != ""){
		document.getElementById("paddingstart").value = paddingstart;
	}
	if(paddingend != ""){
		document.getElementById("paddingend").value = paddingend;
	}
}

function deleteRule(evt, rule_id, reveal) {
	deleteRuleByID(rule_id,false);
	hideReveal(evt, reveal);
}

function deleteRule2(evt, rule_id, reveal) {
	var searchstring = document.getElementById("searchString").value;
	deleteRuleFromSearch(searchstring,rule_id);
	hideReveal(evt, reveal);
}

function reveal(evt, modal) {
	document.getElementById(modal).style.display = "block";
}

function hideReveal(evt, modal) {
	document.getElementById(modal).style.display = 'none';
}

function RuleDeleteReveal(evt, ruleid){
	document.getElementById("RuleDeleteDetails").innerHTML = document.getElementById(ruleid).innerHTML;
	document.getElementById("druleid").value = ruleid;
	reveal(evt, 'RuleDelete');
}

function RecordingDeleteReveal(evt, recordingid, rerecord){
	document.getElementById("RecordingDeleteDetails").innerHTML = document.getElementById(recordingid).innerHTML;
	document.getElementById("drecordingid").value = recordingid;
	document.getElementById("drerecord").value = rerecord;
	reveal(evt, 'RecordingDelete');
}

function submitDeleteRule(){
	rule_id = document.getElementById("druleid").value;
	deleteRuleByID(rule_id, false);
	hideReveal(evt, 'RuleDelete');
}

function submitDeleteRecording(){
	recording_id = document.getElementById("drecordingid").value;
	rerecord = document.getElementById("drerecord").value;
	seriesid = document.getElementById("seriesid").value;
	deleteRecordingByID(recording_id,rerecord,seriesid);
	hideReveal(evt, 'RecordingDelete');
}

function sortRecordings(sortby){
	setCookie("sortby",sortby,3000);
	openRecordingsPage("");
}

function selectSeries(seriesID){
	document.getElementById("series_page").style.display = "none";
	openRecordingsPage(seriesID);
	document.getElementById("recordings_page").style.display = "block";
}

function selectRule(seriesID){
	document.getElementById("series_page").style.display = "none";
	openRulesPage(seriesID);
	document.getElementById("rules_page").style.display = "block";
}
function selectUpcoming(seriesID){
	document.getElementById("series_page").style.display = "none";
	openUpcomingPage(seriesID);
	document.getElementById("upcoming_page").style.display = "block";
}
function viewUpcomingFromRule(seriesID){
	document.getElementById("rules_page").style.display = "none";
	openUpcomingPage(seriesID);
	document.getElementById("upcoming_page").style.display = "block";
}
function viewUpcomingFromSearch(seriesID){
	document.getElementById("search_page").style.display = "none";
	openUpcomingPage(seriesID);
	document.getElementById("upcoming_page").style.display = "block";
}

function handleRecordingType(myRadio){
  var SHOW_YES = "inline-block";
  var SHOW_NO = "none";

  // hide or show each element based on the radio button value
  document.getElementById("typeallselected").style.display = myRadio.value == "all" ? SHOW_YES : SHOW_NO;
  document.getElementById("typerecentselected").style.display = myRadio.value == "recent" ? SHOW_YES : SHOW_NO;
  document.getElementById("typetimeselected").style.display = myRadio.value == "time" ? SHOW_YES : SHOW_NO;
  document.getElementById("typeaftertimeselected").style.display = myRadio.value == "aftertime" ? SHOW_YES : SHOW_NO;

  // due to the newly hidden channel and timestamp fields we need to make sure they are current for the chosen
  // option.   We will do this by sending a change event from the appropriate element
  var elementlookup = {
    "all"       : ["typeallselected",       "optionchannels"],
    "recent"    : ["typerecentselected",    "optionchannels"],
    "time"      : ["typetimeselected",      "optiondates"   ],
    "aftertime" : ["typeaftertimeselected", "optionchannels"]
  }
  var event = new Event('change');
  myRadio.parentElement.children[elementlookup[myRadio.value][0]].children[elementlookup[myRadio.value][1]].dispatchEvent(event);
}

function createQRule(seriesid,recentonly){
	var searchstring = document.getElementById("searchString").value;
	createRuleFromSearch(searchstring,seriesid,recentonly,"30","30",null,null,null);
}

function openTab(evt, tabname) {
	var i, tabcontent, tablinks;
	// get elements with class="tabcontent" and hide
	tabcontent = document.getElementsByClassName("tabcontent");
	for (i=-0; i < tabcontent.length; i++) {
		tabcontent[i].style.display = "none";
	}

	// get elements with class="tablink" and remove the active
	tablinks = document.getElementsByClassName("tablink");
	for (i=0; i < tablinks.length; i++) {
		tablinks[i].className = tablinks[i].className.replace(" active", "");
	}

	// load the page
	if (tabname == 'series_page') {
		openSeriesPage("");
	}
	if (tabname == 'recordings_page') {
		openRecordingsPage("");
	}
	if (tabname == 'rules_page') {
		openRulesPage("");
	}
	if (tabname == 'search_page') {
		openSearchPage();
	}
	if (tabname == 'settings_page') {
		openSettingsPage();
	}
	if (tabname == 'upcoming_page') {
		openUpcomingPage("");
	}

	//show the tablinks
	document.getElementById(tabname).style.display = "block";
	evt.currentTarget.className += " active";
}

/* Set the status message */
function setStatus(msg)
{
	isStatusIdle = 0;
	if(msg == '' || msg == null || msg == undefined)
	{
		isStatusIdle = 1;
		msg = "Idle.";
	}
	document.getElementById('statusMessage').innerHTML = msg;
}

function goSearch()
{
	var str = document.getElementById('searchString').value;
	openSearchPage(str);
}

function deleteRecording(evt, recording_id, reveal) {
	deleteRecordingByID(recording_id,false);
	hideReveal(evt, reveal);
}

function rerecordRecording(evt, recording_id, reveal) {
	deleteRecordingByID(recording_id,true);
	hideReveal(evt, reveal);
}

function setTimeAndChannel(e) {
  var tcArr = e.srcElement.value.split(":");
  document.getElementById("channel").value = tcArr[0];
  document.getElementById("recordtime").value = tcArr[1];
}

function setChannelOnly(e) {
  document.getElementById("channel").value = e.srcElement.value;
  document.getElementById("recordtime").value = "";
}

function initHdhrDvrUi(e) {
  openTab(e,'series_page');
  setInterval(getServerStats,10000);
  getServerStats();
}

function handleSelectArchive(selectarchive){
  var SHOW_YES = "block";
  var SHOW_NO = "none";

  var depends = document.querySelectorAll("ul.postprocessoptions");
  for (var i=0;i<depends.length; i++) {
    depends[i].style.display = selectarchive.checked == true ? SHOW_YES : SHOW_NO;
  }
}

function openNewCreateRuleForm(formId) {
  var form=document.getElementById(formId);
  var title = form.elements.title.value;
  var handler = form.elements.handler.value;
  var subTitle = "";
  if ( handler == "recordings") {
    title = title.replace(/[:;\\\/~?<>*]/g,"");
    subTitle = form.elements.show_title.value.replace(/[:;\\\/~?<>*]/g,"");
    if ( form.elements.show_title.value == "sport"  ) {
      subTitle = "- "+form.elements.show_title.value.replace(/[:;\\\/~?<>*]/g,"");
    }
    else if( form.elements.show_title.value == "movie" ) {
      subTitle = "";
    }
    else {
      subTitle = form.elements.episode.value;
    }
  }
  openCreateRuleForm(form.elements.id.value, form.elements.series_id.value, title, subTitle, form.elements.datetime.value, form.elements.channels.value, form.elements.category.value, handler);
}

function submitRuleForm(){
  var form=document.getElementById("task_edit_form");
  var showtitle = form.elements.seriesname.value;
  var category = form.elements.category.value;
	var seriesid = form.elements.seriesid.value;
	var pstart = form.elements.paddingstart.value + "s";  //add the unit
	var pend = form.elements.paddingend.value + "s";
	var channel = form.elements.channel.value;
	var recordtime = form.elements.recordtime.value;
	var recordafter = form.elements.recordafter.value;
  var comskip = form.elements.comskipprofile.value;
  var archive = form.elements.archive.checked ? "Y" : "N";
  var deleTe = form.elements.deleteoriginal.checked ? "Y" : "N";
  var rebuild = form.elements.overwritearchive.checked ? "Y" : "N";
  var ffmpeg = form.elements.ffmpegprofile.value;
  var handler = form.elements.handler.value;
  var showsubtitle = form.elements.archivepath.value;
	var recentonly = 0;
	var recordtype = "";

  var radios = form.elements.recordtype;
	for (var i = 0, length = radios.length; i < length; i++) {
	  if (radios[i].checked) {
		  recordtype = radios[i].value;
		  break;
	  }
	}
	if(recordtype == "all"){
    recordtime = null;
    recordafter = null;
	}else if(recordtype == "recent"){
    recordtime = null;
    recordafter = null;
    recentonly = 1;
	}else if(recordtype == "time"){
		if(recordtime){
      recordafter = null;
		}else{
			alert("specify a valid date/time for this type of recording");
		}
	}else if(recordtype == "aftertime"){
		if(recordafter){
			recordafter = moment(recordafter).unix();
      recordtime = null;
		}else{
			return alert("specify a valid date for this type of recording");
		}
	}
  if ( category == "" ) category = "Series";
  if ( category == "series" ) category = "Series";
  if ( category == "sport" ) category = "Sports";
  if ( category == "movie" ) category = "Movies";
  if (handler == "recordiings") {
    return aj_call("archiveRecording", new Array( form.elements.archivebutton_id.value,showtitle,showsubtitle,category,comskip,archive,deleTe,rebuild,ffmpeg) );
  }
  else {
    return aj_call("createRulePostProcess", new Array( handler,showtitle,showsubtitle,category,seriesid,recentonly,pstart,pend,channel,recordtime,recordafter,comskip,archive,deleTe,rebuild,ffmpeg) );
  }
}

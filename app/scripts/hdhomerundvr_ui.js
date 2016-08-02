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

function revealRuleForm(evt, modal,seriesid,seriesname){
	reveal(evt, modal);
	document.getElementById("seriesid").value = seriesid;
	document.getElementById("seriesname").value = seriesname;
	getSavedPadding();
}

function handleRecordingType(myRadio){
	if(myRadio.value == "all"){
		document.getElementById("recordtime").style = "display: none;";
		document.getElementById("recordafter").style = "display: none;";
	}else if(myRadio.value == "recent"){
		document.getElementById("recordtime").style = "display: none;";
		document.getElementById("recordafter").style = "display: none;";
	}else if(myRadio.value == "time"){
		document.getElementById("recordtime").style = "display: relative;";
		document.getElementById("recordafter").style = "display: none;";
	}else if(myRadio.value == "aftertime"){
		document.getElementById("recordtime").style = "display: none;";
		document.getElementById("recordafter").style = "display: relative;";
	}
}
function createQRule(seriesid,recentonly){
	var searchstring = document.getElementById("searchString").value;
	createRuleFromSearch(searchstring,seriesid,recentonly,"30","30",null,null,null);
}

function submitCreateRule(){
	var searchstring = document.getElementById("searchString").value;
	var seriesid = document.getElementById("seriesid").value;
	var pstart = document.getElementById("paddingstart").value;
	var pend = document.getElementById("paddingend").value;
	var channel = document.getElementById("channel").value;
	var recordtime = document.getElementById("recordtime").value;
	var recordafter = document.getElementById("recordafter").value;
	var radios = document.getElementsByName('recordtype');
	var recentonly = "";
	var recordtype = "";

	setCookie("paddingstart",pstart,3000);
	setCookie("paddingend",pend,3000);
	
	for (var i = 0, length = radios.length; i < length; i++) {
	    if (radios[i].checked) {
		recordtype = radios[i].value;
		break;
	    }
	}
	if(recordtype == "all"){
		createRuleFromSearch(searchstring,seriesid,0,pstart,pend,channel,null,null);
	}else if(recordtype == "recent"){
		createRuleFromSearch(searchstring,seriesid,1,pstart,pend,channel,null,null);
	}else if(recordtype == "time"){
		if(recordtime){
			recordtime = moment(recordtime).unix();
			createRuleFromSearch(searchstring,seriesid,0,pstart,pend,channel,recordtime,null);
		}else{
			alert("specify a valid date/time for this type of recording");
		}
	}else if(recordtype == "aftertime"){
		if(recordafter){
			recordafter = moment(recordafter).unix();
			createRuleFromSearch(searchstring,seriesid,0,pstart,pend,channel,null,recordafter);
		}else{
			alert("specify a valid date for this type of recording");
		}
	}
	return false;
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
	if (tabname == 'recordings_page') {
		openRecordingsPage();
	}
	if (tabname == 'rules_page') {
		openRulesPage();
	}
	if (tabname == 'search_page') {
		openSearchPage();
	}
	if (tabname == 'hdhr_page') {
		openHDHRPage();
	}
	if (tabname == 'upcoming_page') {
		openUpcomingPage();
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

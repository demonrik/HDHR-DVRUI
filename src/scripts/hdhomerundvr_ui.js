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

function submitDeleteRule(evt){
	rule_id = document.getElementById("druleid").value;
	deleteRuleByID(rule_id, false);
	hideReveal(evt, 'RuleDelete');
}

function submitDeleteRecording(evt){
	recording_id = document.getElementById("drecordingid").value;
	rerecord = document.getElementById("drerecord").value;
	seriesid = document.getElementById("seriesid").value;
	deleteRecordingByID(recording_id,rerecord,seriesid);
	hideReveal(evt, 'RecordingDelete');
}

function revealRuleForm(evt, modal,seriesid,seriesname){
	reveal(evt, modal);
	document.getElementById("seriesid").value = seriesid;
	document.getElementById("seriesname").value = seriesname;
	getSavedPadding();
}

function sortRecordings(sortby){
	setCookie("recSortBy",sortby,3000);
	openRecordingsPage("");
}

function viewSeries(viewmode){
	setCookie("serViewMode",viewmode,3000);
	openSeriesPage("");
}

function viewRecordings(viewmode){
	setCookie("recViewMode",viewmode,3000);
	openRecordingsPage("");
}

function viewUpcoming(viewmode){
	setCookie("upViewMode",viewmode,3000);
	openUpcomingPage("");
}

function viewRules(viewmode){
	setCookie("ruleViewMode",viewmode,3000);
	openRulesPage("");
}

function viewSearch(viewmode){
	setCookie("searchViewMode",viewmode,3000);
	openSearchPage("");
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
	if (tabname == 'dashboard_page') {
		openDashboardPage();
	}
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

function fetchDiags() {
	var form = document.getElementById('diagnostics-form');
	var array = []
	var checkboxes = form.querySelectorAll('input[type=checkbox]:checked')

	for (var i = 0; i < checkboxes.length; i++) {
  		array.push(checkboxes[i].value)
	}
	getDiagnosticInfo(JSON.stringify(array));
	return false;
}

function UpdateSeriesList() {
	var form = document.getElementById('recordings_engines_form');
	var array = []
	var checkboxes = form.querySelectorAll('input[type=checkbox]:checked')

	for (var i = 0; i < checkboxes.length; i++) {
  		array.push(checkboxes[i].value)
	}
	getSeries(JSON.stringify(array));
	return false;
}

function UpdateSeriesInfo(seriesID, storageURL) {
	var array = []
	array.push(seriesID);
	array.push(storageURL);
	getSeriesInfo(JSON.stringify(array));
	return false;
}
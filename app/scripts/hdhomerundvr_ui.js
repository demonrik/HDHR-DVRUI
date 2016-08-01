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
function submitCreateRule(){
	var url = "api.php?api=rules&cmd=create";
	var seriesid = document.getElementById("seriesid").value;
	var paddingstart = document.getElementById("paddingstart").value;
	var paddingend = document.getElementById("paddingend").value;
	var channel = document.getElementById("channel").value;
	var recordtype = document.getElementById("recordtype").value;
	var recordtime = document.getElementById("recordtime").value;
	var recordafter = document.getElementById("recordafter").value;
	var radios = document.getElementsByName('recordtype');

	setCookie("paddingstart",paddingstart,3000);
	setCookie("paddingend",paddingend,3000);
	for (var i = 0, length = radios.length; i < length; i++) {
	    if (radios[i].checked) {
		recordtype = radios[i].value;
		break;
	    }
	}
	url += "&seriesid=" + seriesid;
	url += "&start=" + paddingstart;
	url += "&end=" + paddingend;
	if(channel){
		url += "&channel=" + channel;
	}
	if(recordtype == "all"){
		url += "&recentonly=0";
		createRecording(url);
	}else if(recordtype == "recent"){
		url += "&recentonly=1";
		createRecording(url);
	}else if(recordtype == "time"){
		url += "&recentonly=0";
		if(recordtime){
			recordtime = moment(recordtime).unix();
			url += "&recordtime=" + recordtime;
			createRecording(url);
		}else{
			alert("specify a valid date/time for this type of recording");
		}
	}else if(recordtype == "aftertime"){
		url += "&recentonly=0";
		if(recordafter){
			recordafter = moment(recordafter).unix();
			url += "&recordafter=" + recordafter;
			createRecording(url);
		}else{
			alert("specify a valid date for this type of recording");

		}
	}
	return false;
}

function createRecording(url){
	var result = confirm("Are you sure you want to create this rule?");
	if (result){
		var request = new XMLHttpRequest();
		request.onreadystatechange = function() {
			if (request.readyState == 4 && request.status == 200) {
			   alert("Rule has been successfully created.");
				openSearchPage();
			}
			if (request.readyState == 4 && request.status == 400) {
			   alert("Rule creation failed.  The url was " + url);
			}
		};
		request.open("GET", url, true);
		request.send(null);
	}
}

function confirmDeleteRecording(url){
	var result = confirm("Are you sure you want to delete this recording?\nThis action cannot be undone!");
	if (result){
		var request = new XMLHttpRequest();
		request.onreadystatechange = function() {
			if (request.readyState == 4 && request.status == 200) {
			   alert("Recording deleted successfully.");
				openRecordingsPage();
			}
			if (request.readyState == 4 && request.status == 400) {
			   alert("Deletion failed.  The url was " + url);
			}
		};
		request.open("GET", url, true);
		request.send(null);
	}
}

function confirmDeleteRule(url){
	var result = confirm("Are you sure you want to delete this rule? ");
	if (result){
		var request = new XMLHttpRequest();
		request.onreadystatechange = function() {
			if (request.readyState == 4 && request.status == 200) {
			   alert("Rule has been successfully deleted.");
				openSearchPage();
			}
			if (request.readyState == 4 && request.status == 400) {
			   alert("Rule deletion failed.  The url was " + url);
			}
		};
		request.open("GET", url, true);
		request.send(null);
	}
}

function confirmDeleteRule2(url){
	var result = confirm("Are you sure you want to delete this rule? ");
	if (result){
		var request = new XMLHttpRequest();
		request.onreadystatechange = function() {
			if (request.readyState == 4 && request.status == 200) {
			   alert("Rule has been successfully deleted.");
				openRulesPage();
			}
			if (request.readyState == 4 && request.status == 400) {
			   alert("Rule deletion failed.  The url was " + url);
			}
		};
		request.open("GET", url, true);
		request.send(null);
	}
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

function revealAuth(evt, modal) {
        document.getElementById(modal).style.display = "block";
}

function hideReveal(evt, modal) {
	elm = document.getElementById(modal);
	elm.style.display = 'none';
}

function sleep (time) {
  return new Promise((resolve) => setTimeout(resolve, time));
}

function createRecording(url){
	var result = confirm("Are you sure you want to create this rule?");
	if (result){
		var request = new XMLHttpRequest();
		request.open("GET", url, true);
		request.send(null);
		sleep(500).then(() => {		
			openSearchPage();
		})
	}
}

function confirmDeleteRecording(url){
	var result = confirm("Are you sure you want to delete this recording?\nThis action cannot be undone!");
	if (result){
		var request = new XMLHttpRequest();
		request.open("GET", url, true);
		request.send(null);
		openRecordingsPage();
	}
}

function confirmDeleteRule(url){
	var result = confirm("Are you sure you want to delete this rule? ");
	if (result){
		var request = new XMLHttpRequest();
		request.open("GET", url, true);
		request.send(null);
		openSearchPage();
	}
}

function confirmDeleteRule2(url){
	var result = confirm("Are you sure you want to delete this rule? ");
	if (result){
		var request = new XMLHttpRequest();
		request.open("GET", url, true);
		request.send(null);
		openRulesPage();
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

<?php
	require_once("config/config.php");
	require_once("common/debug.php");
	
	define('TINYAJAX_PATH', 'includes');
	require_once("includes/TinyAjax.php");
	require_once("includes/TinyAjaxBehavior.php");
	require_once("includes/Mobile_Detect.php");

	require_once("functions/dvrui_dashboard.php");
	require_once("functions/dvrui_settings.php");
	require_once("functions/dvrui_diagnostics.php");
	require_once("functions/dvrui_recordings.php");

	/* Prepare Ajax */
	$ajax = new TinyAjax();
	$ajax->setRequestType("POST");    // Change request-type from GET to POST
	$ajax->showLoading();             // Show loading while callback is in progress

	/* Export the PHP Interface */
	$ajax->exportFunction("openDashboardPage","");
	$ajax->exportFunction("openSettingsPage","");
	$ajax->exportFunction("openRecordingsPage","");
	$ajax->exportFunction("clearServerCache","");
	$ajax->exportFunction("getDiagnosticInfo","options");
	$ajax->exportFunction("getSeries","engines");
	$ajax->exportFunction("getSeriesInfo", "seriesInf");

	/* GO */
	$ajax->process(); // Process our callback

	//Build navigation menu for pages
/*	
	$pageTitles = array('Dashboard', 'Series', 'Recordings', 'Upcoming','Rules', 'Search','Diagnostics');
	$pageNames = array('dashboard_page', 'series_page', 'recordings_page', 'upcoming_page', 'rules_page',  'search_page', 'settings_page');
*/

	$pageTitles = array('Dashboard', 'Recordings', 'Upcoming', ' Rules', 'Search', '.');
	$pageNames = array('dashboard_page', 'recordings_page', 'upcoming_age', 'rules_page', 'search_page', 'settings_page');
	$menu_data = file_get_contents('style/pagemenu.html');
	$menuEntries = '';
	for ($i=0; $i < count($pageNames); $i++) {
		$menuEntry = str_replace('<!-- dvrui_menu_pagename-->',$pageNames[$i],file_get_contents('style/pagemenu_entry.html'));
		$menuEntry = str_replace('<!-- dvrui_menu_pagetitle-->',$pageTitles[$i],$menuEntry);
		$menuEntries .= $menuEntry;
	}
	$menu_data = str_replace('<!-- dvrui_pagemenu_entries-->',$menuEntries,$menu_data);
	
	// --- Build Page Here ---
	$pageName = DVRUI_Config::DVRUI_name;
	$UIVersion = "version: " . DVRUI_Config::DVRUI_version;
	$pagecontent = "";

	// --- include header ---
	$detect = new Mobile_Detect;
	if ($detect->isMobile() && !$detect->isTablet()) {
		$stylesheet = 'style/m_style.css';
	}else{	
		$stylesheet = 'style/style.css';
	}
	
	$header = file_get_contents('style/header.html');
	$pagecontent = str_replace('<!-- dvrui_title -->',$pageName,$header);
	$pagecontent = str_replace('<!-- dvrui_stylesheet -->',$stylesheet,$pagecontent);
	$pagecontent = str_replace('<!-- tinyAjax -->',$ajax->drawJavaScript(false, true),$pagecontent);

	// --- Build Body ---
	$indexPage = file_get_contents('style/index_page.html');
	$indexPage = str_replace('<!-- dvrui_title -->',$pageName,$indexPage);
	$indexPage = str_replace('<!-- dvrui_version -->',$UIVersion,$indexPage);

	$dashboarddata = file_get_contents('style/dashboard.html');
	$settingsdata = file_get_contents('style/settings.html');
	$recordingsdata = file_get_contents('style/recordings.html');

	$indexPage = str_replace('<!-- dvrui_pagemenu -->',$menu_data,$indexPage);
	$indexPage = str_replace('<!-- dvrui_dashboard -->',$dashboarddata,$indexPage);
	$indexPage = str_replace('<!-- dvrui_settingslist -->',$settingsdata,$indexPage);
	$indexPage = str_replace('<!-- dvrui_recordingslist -->',$recordingsdata,$indexPage);

	// -- Attach the Index to the Page
	$pagecontent .= $indexPage;
	
	// --- include footer ---
	$footer = file_get_contents('style/footer.html');
	$pagecontent .= $footer;
	echo($pagecontent);

	error_log( "======= Debug Log END =========" );
?>
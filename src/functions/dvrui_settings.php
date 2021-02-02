<?php
require_once("common/common.php");

function openSettingsPage() {
	// prep
	ob_start();
	$tab = new TinyAjaxBehavior();

	//create output
	$htmlStr = displayConfig();
		
	//get data
	$result = ob_get_contents();
	ob_end_clean();

	//display
	$tab->add(TabInnerHtml::getBehavior("settings_box", $htmlStr));
	return $tab->getString();
}

function clearServerCache() {
	clearCache();
	openSettingsPage();
}


function displayConfig() {
	$htmlStr  = "Settings will be visible here";
	return $htmlStr;
}


		
function saveConfig($newcfg) {

}


function displayDiagnostics() {
	$htmlStr = "";
	return $htmlStr;
}


?>
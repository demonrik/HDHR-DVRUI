<?php
	require_once("TinyAjaxBehavior.php");
	require_once("vars.php");
	require_once("includes/dvrui_hdhrjson.php");
	require_once("includes/dvrui_common.php");

	function openClearCache() {
		clearCache();
		openSettingsPage();

	}
	
	function openSettingsPage() {
		// prep
		ob_start();
		$tab = new TinyAjaxBehavior();

		//create output
		$htmlStr = "";
		
		//get data
		$result = ob_get_contents();
		ob_end_clean();

		//display
		$tab->add(TabInnerHtml::getBehavior("settings_box", $htmlStr));
		return $tab->getString();
	}

?>

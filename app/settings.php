<?php
	require_once("TinyAjaxBehavior.php");
	require_once("vars.php");
	require_once("includes/dvrui_hdhrjson.php");
	require_once("includes/dvrui_common.php");
	require_once("includes/dvrui_config.php");

	function openClearCache() {
		clearCache();
		openSettingsPage();
	}
	
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

	function displayConfig() {
		$config = new DVRUI_Config();
		$cfg = $config->getConfig();
		$paramStr = file_get_contents('style/config_param.html');
		
		$htmlStr  = "";

		foreach($cfg as $param) {
			$paramEntry = str_replace('<!-- dvrui_param_name -->', $param['name'], $paramStr);
			$paramEntry = str_replace('<!-- dvrui_param_value -->', $param['val'], $paramEntry);
			$htmlStr.= $paramEntry;
		}
		return $htmlStr;
	}
		
	
	function saveConfig($newcfg) {
		$config = new DVRUI_Config();
	}

?>

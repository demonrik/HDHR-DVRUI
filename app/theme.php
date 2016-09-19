<?php
	require_once("TinyAjaxBehavior.php");
	require_once("vars.php");
	require_once("tools/lessc.inc.php");
	require_once("includes/Mobile_Detect.php");

	function applyDefaultTheme() {
		$less = new lessc();
		try {
			$less->checkedCompile("./themes/default/main.less","./themes/default/style.css");
			$less->checkedCompile("./themes/default/m_main.less","./themes/default/m_style.css");
		} catch (exception $e) {
			echo ($e->getMessage());
		}
	}

	function getTheme() {
		$detect = new Mobile_Detect;
		applyDefaultTheme();

		if ($detect->isMobile() && !$detect->isTablet()) {
			return "themes/default/m_style.css";
		}else{	
			return "themes/default/style.css";
		}
	}
?>

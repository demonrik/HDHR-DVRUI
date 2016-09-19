<?php
	require_once("TinyAjaxBehavior.php");
	require_once("vars.php");
	require_once("tools/lessc.inc.php");
	require_once("includes/Mobile_Detect.php");

	function getThemeName(){
		if(isset($_COOKIE['theme'])){
			$theme = $_COOKIE['theme'];
		}else{
			$theme = "default";
		}
		return $theme;
	}

	function compileTheme($theme) {
		$less = new lessc();
		try {
			$less->checkedCompile("./themes/$theme/main.less","./themes/$theme/style.css");
			$less->checkedCompile("./themes/$theme/m_main.less","./themes/$theme/m_style.css");
		} catch (exception $e) {
			echo ($e->getMessage());
		}
	}

	function getTheme() {
		$detect = new Mobile_Detect;
		$theme = getThemeName();
		compileTheme($theme);
		if ($detect->isMobile() && !$detect->isTablet()) {
			return "themes/$theme/m_style.css";
		}else{	
			return "themes/$theme/style.css";
		}
	}
?>

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
	
	function autoCompileTheme($inputFile, $outputFile){
		$cachefile = $inputFile . '.cache';
		if (file_exists($cachefile)) {
			$cache = unserialize(file_get_contents($cachefile));
		} else {
			$cache = $inputFile;
		}
  	$less = new lessc;
  	$newCache = $less->cachedCompile($cache);

	  if (!is_array($cache) || $newCache["updated"] > $cache["updated"]) {
    	file_put_contents($cacheFile, serialize($newCache));
    	file_put_contents($outputFile, $newCache['compiled']);
  	}
	}

	function compileTheme($theme) {
		try {
			autoCompileTheme("./themes/$theme/main.less","./themes/$theme/style.css");
			autoCompileTheme("./themes/$theme/m_main.less","./themes/$theme/m_style.css");
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

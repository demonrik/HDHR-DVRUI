<?php
	require_once("TinyAjaxBehavior.php");
	require_once("vars.php");
	require_once("tools/lessc.inc.php");

	function applyDefaultTheme() {
		$less = new lessc();
		try {
			$less->checkedCompile("./style/main.less","./style/style.css");
		} catch (exception $e) {
			echo ($e->getMessage());
		}
	}

?>
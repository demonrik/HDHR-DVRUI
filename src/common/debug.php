<?php
	error_reporting(E_ALL & ~(E_DEPRECATED | E_STRICT));
	require_once("config/config.php");

	if (function_exists('opcache_reset') && DVRUI_Config::DVRUI_DEBUG) {
		opcache_reset();
	}

	if (DVRUI_Config::DVRUI_DEBUG) {
		ini_set("log_errors", DVRUI_Config::DVRUI_DEBUG);
		ini_set("error_log", DVRUI_Config::DVRUI_tmp . "dvrui.log");
		ini_set('log_errors_max_len', 1024);
		error_log( "======= Debug Log START =========" );
		error_log( "DVRUI Version: " . DVRUI_Config::DVRUI_version);
	}
	
	if (PHP_MAJOR_VERSION >= 7) {
		error_log( "PHP > 7 detected" );
		set_error_handler(function ($errno, $errstr) {
			return strpos($errstr, 'Declaration of') === 0;
		}, E_WARNING);
	}
?>
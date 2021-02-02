<?php
final class DVRUI_Config
{
	const DVRUI_version	="0.9";
	const DVRUI_name    ="HDHomeRun DVR UI";
	const DVRUI_git     ="https://github.com/demonrik/HDHR-DVRUI.git";
	const DVRUI_apiurl  ="http://api.hdhomerun.com/";	
	const DVRUI_tmp		="tmp/";	

	/* Set TZ to something valid from http://php.net/manual/en/timezones.php */
	const DVRUI_TZ		= '';

	/* json cache in seconds */
	const DVRUI_cache = 300; // 5 minutes
	
	const DVRUI_DEBUG = true;
}
?>

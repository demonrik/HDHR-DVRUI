<?php
final class DVRUI_Vars 
{
	const DVRUI_version	="0.8.3";
	const DVRUI_name    ="HDHomeRun DVR UI";
	const DVRUI_git     ="https://github.com/demonrik/HDHR-DVRUI.git";
	const DVRUI_apiurl  ="http://api.hdhomerun.com/";	

	/* Set TZ to something valid from http://php.net/manual/en/timezones.php */
	const DVRUI_TZ		= '';

	/* json cache in seconds */
	const DVRUI_discover_cache = 360;
	const DVRUI_upcoming_cache = 3600;
	
	const DVRUI_DEBUG = false;
}
?>

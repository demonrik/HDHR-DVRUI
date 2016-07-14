<?php
	require_once('vars.php');
	
	function DVRUI_setTZ() {
		if (DVRUI_Vars::DVRUI_TZ != '') {
			echo('using vars TZ');
			if (date_default_timezone_set(DVRUI_Vars::DVRUI_TZ)) {
				return;
			}
		}
		
		/* date_default_timezone_get returns TZ in this order
		 * - set by date_default_timezone_set()
		 * - reading TZ env variable (PHP <5.4.0)
		 * - reading date.timezone from php.ini if set
		 * - query host OS (php < 5.4.0)
		 * - otherwise return UTC
		 */
		$tz = date_default_timezone_get();
		date_default_timezone_set($tz);
	}

?>
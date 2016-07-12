<?php
	require_once("vars.php");
	require_once("includes/dvrui_hdhrjson.php");

	function getLatestHDHRStatus() {
		
		$hdhr = DVRUI_Vars::DVR_qpkgPath . '/' . DVRUI_Vars::DVR_bin;
		$DVRBin = new DVRUI_HDHRbintools($hdhr);
		$statusmsg = $DVRBin->get_DVR_status();

		$lastline='';
		$configFile = new DVRUI_Engine_Config();
		$logList = new DVRUI_Engine_LogList($configFile->getRecordPath());
		$logfile = $logList->getNewestLogFile();
		if (file_exists($logfile)) {
			$lines = file($logfile);
			$linecount = count($lines);
			$lastline = $lines[$linecount - 1];
		}
		
		$htmlstr = preg_replace('/\s+/', ' ', trim($statusmsg . ' | ' . $lastline));
		return $htmlstr;
	}

?>
<?php
	require_once("TinyAjaxBehavior.php");
	require_once("vars.php");
	require_once("statusmessage.php");
	require_once("includes/dvrui_recordengine_logfile.php");
	require_once("includes/dvrui_recordengine_config.php");

	function openLogPage() {
		// prep
		ob_start();
		$tab = new TinyAjaxBehavior();

		//create output
		//$htmlStr = getLogFileList($configFile->getRecordPath());
		$configFile = new DVRUI_Engine_Config();
		$htmlStr = '<h4>Discovered Logfiles</h4>';
		$htmlStr.= getLogFileList($configFile->getRecordPath());
		
		//get data
		$result = ob_get_contents();
		ob_end_clean();

		// get latest status	
		$statusmsg = getLatestHDHRStatus();
	
		//display
		$tab->add(TabInnerHtml::getBehavior("loglist", $htmlStr));
		if ($result != '' && $result != NULL)
			$tab->add(TabInnerHtml::getBehavior("statusMessage", $result));
		else
			$tab->add(TabInnerHtml::getBehavior("statusMessage", $statusmsg));
		return $tab->getString();
	}

	function getLogFile($filename) {
		// prep
		ob_start();
		$tab = new TinyAjaxBehavior();
		$configFile = new DVRUI_Engine_Config();
		//create output
		$logfile = new DVRUI_Engine_LogFile($configFile->getRecordPath() . '/' . $filename);
		$logEntry = file_get_contents('style/logfile_entry.html');
		$htmlStr = '';

		for ($i=0; $i < $logfile->getNumEntry(); $i++) {
			$entry = $logfile->getEntryAt($i);
			$entryStr = str_replace('<!--log-time-->',$entry['Timestamp'],$logEntry);
			
			if ($logfile->isTypeError($i)) {
				$entryStr = str_replace('<!--logtype-class-->','logTypeError',$entryStr);
			} else if ($logfile->isTypeStatus($i)) {
				$entryStr = str_replace('<!--logtype-class-->','logTypeStatus',$entryStr);
			} else if ($logfile->isTypePlayback($i)) {
				$entryStr = str_replace('<!--logtype-class-->','logTypePlayback',$entryStr);
			} else if ($logfile->isTypeRecording($i)) {
				$entryStr = str_replace('<!--logtype-class-->','logTypeRecording',$entryStr);
			} else if ($logfile->isTypeRecorded($i)) {
				$entryStr = str_replace('<!--logtype-class-->','logTypeRecorded',$entryStr);
			} else {
				$entryStr = str_replace('<!--logtype-class-->','logTypeInfo',$entryStr);
			}
			
			$entryStr = str_replace('<!--log-type-->',$entry['Type'],$entryStr);
			$entryStr = str_replace('<!--logsubtype-class-->','',$entryStr);
			$entryStr = str_replace('<!--log-subtype-->',$entry['SubType'],$entryStr);
			if ($logfile->infoContainsError($i)) {
				$entryStr = str_replace('<!--logentry-class-->','logInfoError',$entryStr);
			} else {
				$entryStr = str_replace('<!--logentry-class-->','',$entryStr);
			}
			$entryStr = str_replace('<!--log-entry-->',$entry['Info'],$entryStr);
			$htmlStr.= $entryStr;
		}
	
		$statusmsg = getLatestHDHRStatus();
		//get data
		$result = ob_get_contents();
		ob_end_clean();
	
		//display
		$tab->add(TabInnerHtml::getBehavior("logfile_box", $htmlStr));
		if ($result != '' && $result != NULL)
			$tab->add(TabInnerHtml::getBehavior("statusMessage", $result));
		else
			$tab->add(TabInnerHtml::getBehavior("statusMessage", $statusmsg));
		return $tab->getString();
	}

	function rmLogFile($filename) {
		// prep
		ob_start();
		$tab = new TinyAjaxBehavior();
		$configFile = new DVRUI_Engine_Config();
	
		//create output
		$logfile = $configFile->getRecordPath() . '/' . $filename;
		$htmlStr = 'Deleting ' . $logfile;
		if (file_exists($logfile)) {
			$del = unlink($logfile);
		}

		$logFileList = getLogFileList($configFile->getRecordPath());
		$statusmsg = getLatestHDHRStatus();
		
		//get data
		$result = ob_get_contents();
		ob_end_clean();
	
		//display
		$tab->add(TabInnerHtml::getBehavior("loglist", $logFileList));
		$tab->add(TabInnerHtml::getBehavior("logfile_box", $htmlStr));
		if ($result != '' && $result != NULL)
			$tab->add(TabInnerHtml::getBehavior("statusMessage", $result));
		else
			$tab->add(TabInnerHtml::getBehavior("statusMessage", $statusmsg));
		return $tab->getString();
	}
	
	function getLogFileList($logPath) {
		$listStr = '';
		$logList = new DVRUI_Engine_LogList($logPath);
		$logListEntry = file_get_contents('style/loglist_entry.html');
		if ($logList->pathExists()) {
			$listStr = '<ul>';
			for ($i = $logList->getListLength() - 1 ; $i >= 0 ; $i--) {
				$logfile = basename($logList->getNextLogFile($i),'.log');
				$logfullname = basename($logList->getNextLogFile($i));
				$logEntry = str_replace('<!--logfile-name -->',$logfile,$logListEntry);
				$logEntry = str_replace('<!--logfile-fname -->',$logfullname,$logEntry);
				$listStr .= $logEntry;
			}
			$listStr .= '</ul>';
		} else {
			$listStr = "ERROR: recording path is invalid";
		}
		return $listStr;
	}
?>

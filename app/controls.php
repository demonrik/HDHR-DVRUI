<?php
	require_once("TinyAjaxBehavior.php");
	require_once("vars.php");
	require_once("statusmessage.php");
	require_once("includes/dvrui_hdhrcontrols.php");
	
	function changeDvrState($option) {
		// prep
		ob_start();
		$tab = new TinyAjaxBehavior();
	
		//create output
		$hdhr = new DVRUI_HDHRcontrols(DVRUI_Vars::DVR_qpkgPath . '/' . DVRUI_Vars::DVR_sh);
		switch ($option) {
			case 'start':
				$htmlStr = 'Starting up the DVR record engine';
				if ($hdhr->start_DVR())
					$htmlStr .= ' - DONE';
				break;
			case 'stop':
				$htmlStr = 'Shutting down the DVR record engine';
				if ($hdhr->shutdown_DVR())
					$htmlStr .= ' - DONE';
				break;
			case 'restart':
				$htmlStr = 'Restarting the DVR record engine';
				if ($hdhr->restart_DVR())
					$htmlStr .= ' - DONE';
				break;
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

?>
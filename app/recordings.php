<?php
	require_once("TinyAjaxBehavior.php");
	require_once("vars.php");
	require_once("statusmessage.php");
	require_once("includes/dvrui_hdhrjson.php");
	require_once("includes/dvrui_recordings.php");
	
	function openRecordingsPage() {
		// prep
		ob_start();
		$tab = new TinyAjaxBehavior();

		//create output
		$hdhr = new DVRUI_HDHRjson();
		$hdhrRecordings = new DVRUI_Recordings($hdhr);
		$hdhrRecordings->sortRecordingsByTitle();
		$numRecordings = $hdhrRecordings->getRecordingCount();
		$htmlStr = processRecordingData($hdhrRecordings, $numRecordings);
		echo('check'); /*
		*/
		//get data
		$result = ob_get_contents();
		ob_end_clean();

		// get latest status	
		$statusmsg = getLatestHDHRStatus();
	
		//display
		$tab->add(TabInnerHtml::getBehavior("recordings_box", $htmlStr));
		if ($result != '' && $result != NULL)
			$tab->add(TabInnerHtml::getBehavior("statusMessage", $result));
		else
			$tab->add(TabInnerHtml::getBehavior("statusMessage", $statusmsg));
		return $tab->getString();
	}

	function deleteRecordingByID($id,$rerecord) {
		// prep
		ob_start();
		$tab = new TinyAjaxBehavior();

		//create output
		$hdhr = new DVRUI_HDHRjson();
		$hdhrRecordings = new DVRUI_Recordings($hdhr);
		$hdhrRecordings->deleteRecording($id,$rerecord);

		$numRecordings = $hdhrRecordings->getRecordingCount();
		$htmlStr = processRecordingData($hdhrRecordings, $numRecordings);

		//get data
		$result = ob_get_contents();
		ob_end_clean();

		// get latest status	
		$statusmsg = getLatestHDHRStatus();
	
		//display
		$tab->add(TabInnerHtml::getBehavior("recordings_box", $htmlStr));
		if ($result != '' && $result != NULL)
			$tab->add(TabInnerHtml::getBehavior("statusMessage", $result));
		else
			$tab->add(TabInnerHtml::getBehavior("statusMessage", $statusmsg));
		return $tab->getString();
	}

	function processRecordingData($hdhrRecordings, $numRecordings) {
		$recordingsData = '';
		for ($i=0; $i < $numRecordings; $i++) {
			$recordingsEntry = file_get_contents('style/recordings_entry.html');
			$recordingsEntry = str_replace('<!-- dvr_recordings_image -->',$hdhrRecordings->getRecordingImage($i),$recordingsEntry);
			$recordingsEntry = str_replace('<!-- dvr_recordings_episode -->',$hdhrRecordings->getEpisodeNumber($i),$recordingsEntry);
			$recordingsEntry = str_replace('<!-- dvr_recordings_show -->',$hdhrRecordings->getEpisodeTitle($i),$recordingsEntry);
			$recordingsEntry = str_replace('<!-- dvr_recordings_title -->',$hdhrRecordings->getTitle($i),$recordingsEntry);
			$recordingsEntry = str_replace('<!-- dvr_recordings_synopsis -->',$hdhrRecordings->getSynopsis($i),$recordingsEntry);
			$recordingsEntry = str_replace('<!-- dvr_recordings_play -->',$hdhrRecordings->get_PlayURL($i),$recordingsEntry);
			$recordingsEntry = str_replace('<!-- dvr_recordings_recstart -->',$hdhrRecordings->getRecordStartTime($i),$recordingsEntry);
			$recordingsEntry = str_replace('<!-- dvr_recordings_start -->',$hdhrRecordings->getStartTime($i),$recordingsEntry);
			$recordingsEntry = str_replace('<!-- dvr_recordings_originaldate -->',$hdhrRecordings->getOriginalAirDate($i),$recordingsEntry);
			$recordingsEntry = str_replace('<!-- dvr_recordings_repeat -->',$hdhrRecordings->isRepeat($i),$recordingsEntry);
			$recordingsEntry = str_replace('<!-- dvr_recordings_chname -->',$hdhrRecordings->getChannelName($i),$recordingsEntry);
			$recordingsEntry = str_replace('<!-- dvr_recordings_chnumber -->',$hdhrRecordings->getChannelNumber($i),$recordingsEntry);
			$recordingsEntry = str_replace('<!-- dvr_recordings_chaffiliate -->',$hdhrRecordings->getChannelAffiliate($i),$recordingsEntry);

			$revealContent = file_get_contents('style/reveal_episode.html');
			$revealContent = str_replace('<!-- dvr_recordings_image -->',$hdhrRecordings->getRecordingImage($i),$revealContent);
			$revealContent = str_replace('<!-- dvr_recordings_episode -->',$hdhrRecordings->getEpisodeNumber($i),$revealContent);
			$revealContent = str_replace('<!-- dvr_recordings_show -->',$hdhrRecordings->getEpisodeTitle($i),$revealContent);
			$revealContent = str_replace('<!-- dvr_recordings_title -->',$hdhrRecordings->getTitle($i),$revealContent);
			$revealContent = str_replace('<!-- dvr_recordings_synopsis -->',$hdhrRecordings->getSynopsis($i),$revealContent);
			$revealContent = str_replace('<!-- dvr_recordings_recstart -->',$hdhrRecordings->getRecordStartTime($i),$revealContent);
			$revealContent = str_replace('<!-- dvr_recordings_start -->',$hdhrRecordings->getStartTime($i),$revealContent);
			$revealContent = str_replace('<!-- dvr_recordings_originaldate -->',$hdhrRecordings->getOriginalAirDate($i),$revealContent);
			$revealContent = str_replace('<!-- dvr_recordings_repeat -->',$hdhrRecordings->isRepeat($i),$revealContent);
			$revealContent = str_replace('<!-- dvr_recordings_chname -->',$hdhrRecordings->getChannelName($i),$revealContent);
			$revealContent = str_replace('<!-- dvr_recordings_chnumber -->',$hdhrRecordings->getChannelNumber($i),$revealContent);
			$revealContent = str_replace('<!-- dvr_recordings_chaffiliate -->',$hdhrRecordings->getChannelAffiliate($i),$revealContent);

			$revealDelID = 'RulesAuth_d' . $i;
			$recordingsEntry = str_replace('<!-- dvrui_reveal_delete -->',$revealDelID,$recordingsEntry);

			$revealDelTitle = 'Delete Permanently ' . $hdhrRecordings->getTitle($i) . ' ' . $hdhrRecordings->getEpisodeNumber($i) . '?';
			$revealDel = file_get_contents('style/reveal_2btns.html');
			$revealDel = str_replace('<!-- drvui_reveal_title -->', $revealDelTitle ,$revealDel);
			$revealDel = str_replace('<!-- drvui_reveal_content -->', $revealContent,$revealDel);
			$revealDel = str_replace('<!-- dvrui_reveal -->',$revealDelID,$revealDel);
			$revealDel = str_replace('<!-- dvr_reveal_btn1_title -->','Cancel',$revealDel);
			$revealDel = str_replace('<!-- dvr_reveal_btn1_func -->',"hideReveal(event,'" . $revealDelID . "');" ,$revealDel);
			$revealDel = str_replace('<!-- dvr_reveal_btn2_title -->','Delete',$revealDel);
			$revealDel = str_replace('<!-- dvr_reveal_btn2_func -->',"deleteRecording(event, '" . $hdhrRecordings->getRecordingID($i) . "','" . $revealDelID ."')",$revealDel);

			$revealReRecID = 'RulesAuth_r' . $i;
			$recordingsEntry = str_replace('<!-- dvrui_reveal_rerecord -->', $revealReRecID, $recordingsEntry);

			$revealReRecTitle = 'Delete & Rerecord ' . $hdhrRecordings->getTitle($i) . ' ' . $hdhrRecordings->getEpisodeNumber($i) . '?';
			$revealRec = file_get_contents('style/reveal_2btns.html');
			$revealRec = str_replace('<!-- drvui_reveal_title -->', $revealReRecTitle ,$revealRec);
			$revealRec = str_replace('<!-- drvui_reveal_content -->', $revealContent,$revealRec);
			$revealRec = str_replace('<!-- dvrui_reveal -->',$revealReRecID,$revealRec);
			$revealRec = str_replace('<!-- dvr_reveal_btn1_title -->','Cancel',$revealRec);
			$revealRec = str_replace('<!-- dvr_reveal_btn1_func -->',"hideReveal(event,'" . $revealReRecID ."');" ,$revealRec);
			$revealRec = str_replace('<!-- dvr_reveal_btn2_title -->','ReRecord',$revealRec);
			$revealRec = str_replace('<!-- dvr_reveal_btn2_func -->',"rerecordRecording(event, '" . $hdhrRecordings->getRecordingID($i) . "','" . $revealReRecID ."')",$revealRec);

			$recordingsData .= $recordingsEntry;
			$recordingsData .= $revealDel;
			$recordingsData .= $revealRec;
		}
		$recordingsList = file_get_contents('style/recordings_list.html');
		$recordingsList = str_replace('<!-- dvr_recordings_count -->','Found: ' . $numRecordings . ' Recordings<br/>',$recordingsList);
		$recordingsList = str_replace('<!-- dvr_recordings_list -->',$recordingsData,$recordingsList);
		
		return $recordingsList;
	}
	
?>

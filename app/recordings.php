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
		$htmlStr = getRecordings();

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

	function getRecordings() {
		$recordingsStr = '';
		// Discover Recording
		$hdhr = new DVRUI_HDHRjson();
		$hdhrRecordings = new DVRUI_Recordings($hdhr);
		$numRecordings = $hdhrRecordings->getRecordingCount();
		$recordingsData = '';
		for ($i=0; $i < $numRecordings; $i++) {
			$recordingsEntry = file_get_contents('style/recordings_entry.html');
			$recordingsEntry = str_replace('<!-- dvr_recordings_image -->',$hdhrRecordings->getRecordingImage($i),$recordingsEntry);
			$recordingsEntry = str_replace('<!-- dvr_recording_id -->',$i,$recordingsEntry);
			$recordingsEntry = str_replace('<!-- dvr_recordings_episode -->',$hdhrRecordings->getEpisodeNumber($i),$recordingsEntry);
			$recordingsEntry = str_replace('<!-- dvr_recordings_show -->',$hdhrRecordings->getEpisodeTitle($i),$recordingsEntry);
			$recordingsEntry = str_replace('<!-- dvr_recordings_title -->',$hdhrRecordings->getTitle($i),$recordingsEntry);
			$recordingsEntry = str_replace('<!-- dvr_recordings_synopsis -->',$hdhrRecordings->getSynopsis($i),$recordingsEntry);
			$recordingsEntry = str_replace('<!-- dvr_recordings_play -->',$hdhrRecordings->get_PlayURL($i),$recordingsEntry);
			$recordingsEntry = str_replace('<!-- dvr_recordings_delete -->',$hdhrRecordings->getDeleteCmdURL($i),$recordingsEntry);
			$recordingsEntry = str_replace('<!-- dvr_recordings_rerec -->',$hdhrRecordings->getRerecordCmdURL($i),$recordingsEntry);
			$recordingsData .= $recordingsEntry;
		}
		$recordingsList = file_get_contents('style/recordings_list.html');
		$recordingsList = str_replace('<!-- dvr_storage_url -->','Processing Recordings From: ' . $hdhr->get_storage_url() . '<br/>',$recordingsList);
		$recordingsList = str_replace('<!-- dvr_recordings_count -->','Found: ' . $numRecordings . ' Recordings<br/>',$recordingsList);
		$recordingsList = str_replace('<!-- dvr_recordings_list -->',$recordingsData,$recordingsList);
		
		return $recordingsList;
	}
?>

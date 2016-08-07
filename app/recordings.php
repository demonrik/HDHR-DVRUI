<?php
	require_once("TinyAjaxBehavior.php");
	require_once("vars.php");
	require_once("statusmessage.php");
	require_once("includes/dvrui_hdhrjson.php");
	require_once("includes/dvrui_recordings.php");

	function getSort(){
		if(isset($_COOKIE['sortby'])){
			$sortby = $_COOKIE['sortby'];
		}else{
			$sortby = "DD";
		}
		return $sortby;
	}
	
	function openRecordingsPage() {
		// prep
		ob_start();
		$tab = new TinyAjaxBehavior();

		//create output
		$hdhr = new DVRUI_HDHRjson();
		$hdhrRecordings = new DVRUI_Recordings($hdhr);
		$sortby = getSort();
		$hdhrRecordings->sortRecordings($sortby);
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

	function deleteRecordingByID($id,$rerecord) {
		// prep
		ob_start();
		$tab = new TinyAjaxBehavior();

		//create output
		$hdhr = new DVRUI_HDHRjson();
		$hdhrRecordings = new DVRUI_Recordings($hdhr);
		$hdhrRecordings->deleteRecording($id,$rerecord);
		$hdhrRecordings->sortRecordings('DD');

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
			$recordingsEntry = str_replace('<!-- dvr_recordings_id -->',$hdhrRecordings->getRecordingID($i),$recordingsEntry);
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



			$recordingsData .= $recordingsEntry;
		}
		$recordingsList = file_get_contents('style/recordings_list.html');
		$revealContent = file_get_contents('style/recordingdeletereveal.html');
		$recordingsList = str_replace('<!-- dvr_recordings_count -->','Found: ' . $numRecordings . ' Recordings',$recordingsList);
		$recordingsList = str_replace('<!-- dvr_recordings_list -->',$recordingsData,$recordingsList);
		$recordingsList .= $revealContent;	
		return $recordingsList;
	}
	
?>

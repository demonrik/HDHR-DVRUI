<?php
	require_once("TinyAjaxBehavior.php");
	require_once("vars.php");
	require_once("includes/dvrui_hdhrjson.php");
	require_once("includes/dvrui_recordings.php");

	function getRecordingSort(){
		if(isset($_COOKIE['recSortBy'])){
			$sortby = $_COOKIE['recSortBy'];
		}else{
			$sortby = "DD";
		}
		return $sortby;
	}

	function getRecordingViewMode(){
		if(isset($_COOKIE['recViewMode'])){
			$viewmode = $_COOKIE['recViewMode'];
		}else{
			$viewmode = "tile";
		}
		return $viewmode;
	}
	
	function openRecordingsPage($seriesid) {
		// prep
		ob_start();
		$tab = new TinyAjaxBehavior();

		//create output
		$hdhr = new DVRUI_HDHRjson();
		$hdhrRecordings = new DVRUI_Recordings($hdhr);
		if(strlen($seriesid) > 3){
			$hdhrRecordings->processSeriesRecordings($hdhr, $seriesid);
		}else{
			$hdhrRecordings->processAllRecordings($hdhr);
		}		
		$sortby = getRecordingSort();
		$hdhrRecordings->sortRecordings($sortby);
		$numRecordings = $hdhrRecordings->getRecordingCount();
		$htmlStr = processRecordingData($hdhrRecordings, $numRecordings, $seriesid);
		//get data
		$result = ob_get_contents();
		ob_end_clean();

		//display
		$tab->add(TabInnerHtml::getBehavior("recordings_box", $htmlStr));
		return $tab->getString();
	}

	function deleteRecordingByID($id,$rerecord,$seriesid) {
		// prep
		ob_start();
		$tab = new TinyAjaxBehavior();

		//create output
		$hdhr = new DVRUI_HDHRjson();

		//do the delete
		$hdhrDel = new DVRUI_Recordings($hdhr);
		$hdhrDel->processAllRecordings($hdhr);
		$hdhrDel->deleteRecording($id,$rerecord);
		
		//refresh list of recordings
		$hdhrRecordings = new DVRUI_Recordings($hdhr);
		if(strlen($seriesid) > 3){
			$hdhrRecordings->processSeriesRecordings($hdhr, $seriesid);
		}else{
			$hdhrRecordings->processAllRecordings($hdhr);
		}		
		$sortby = getRecordingSort();
		$hdhrRecordings->sortRecordings($sortby);

		$numRecordings = $hdhrRecordings->getRecordingCount();
		$htmlStr = processRecordingData($hdhrRecordings, $numRecordings, $seriesid);

		//get data
		$result = ob_get_contents();
		ob_end_clean();
	
		//display
		$tab->add(TabInnerHtml::getBehavior("recordings_box", $htmlStr));
		return $tab->getString();
	}

	function processRecordingData($hdhrRecordings, $numRecordings, $seriesid) {
		$viewmode = getRecordingViewMode();
		$recordingsData = '';
		for ($i=0; $i < $numRecordings; $i++) {
			if (strcasecmp($viewmode,"list")==0) {
				$recordingsEntry = file_get_contents('style/recordings_entry_list.html');
			} else {
				$recordingsEntry = file_get_contents('style/recordings_entry_tile.html');
			}
			$recordingsEntry = str_replace('<!-- dvr_recordings_id -->',$hdhrRecordings->getRecordingID($i),$recordingsEntry);
			$recordingsEntry = str_replace('<!-- dvr_series_id -->',$hdhrRecordings->getSeriesID($i),$recordingsEntry);
			$recordingsEntry = str_replace('<!-- dvr_recordings_image -->',$hdhrRecordings->getRecordingImage($i),$recordingsEntry);
			$recordingsEntry = str_replace('<!-- dvr_recordings_episode -->',$hdhrRecordings->getEpisodeNumber($i),$recordingsEntry);
			$recordingsEntry = str_replace('<!-- dvr_recordings_show -->',$hdhrRecordings->getEpisodeTitle($i),$recordingsEntry);
			$recordingsEntry = str_replace('<!-- dvr_recordings_title -->',$hdhrRecordings->getTitle($i),$recordingsEntry);
			$recordingsEntry = str_replace('<!-- dvr_recordings_play -->',$hdhrRecordings->get_PlayURL($i),$recordingsEntry);
			$recordingsEntry = str_replace('<!-- dvr_recordings_recstart -->',$hdhrRecordings->getRecordStartTime($i),$recordingsEntry);
			$recordingsEntry = str_replace('<!-- dvr_recordings_start -->',$hdhrRecordings->getStartTime($i),$recordingsEntry);
			$recordingsEntry = str_replace('<!-- dvr_recordings_originaldate -->',$hdhrRecordings->getOriginalAirDate($i),$recordingsEntry);
			$recordingsEntry = str_replace('<!-- dvr_recordings_repeat -->',$hdhrRecordings->isRepeat($i),$recordingsEntry);
			$recordingsEntry = str_replace('<!-- dvr_recordings_chname -->',$hdhrRecordings->getChannelName($i),$recordingsEntry);
			$recordingsEntry = str_replace('<!-- dvr_recordings_chnumber -->',$hdhrRecordings->getChannelNumber($i),$recordingsEntry);
			$recordingsEntry = str_replace('<!-- dvr_recordings_chaffiliate -->',$hdhrRecordings->getChannelAffiliate($i),$recordingsEntry);
			$recordingsEntry = str_replace('<!-- dvr_recordings_synopsis -->',$hdhrRecordings->getSynopsis($i),$recordingsEntry);

			$recordingsData .= $recordingsEntry;
		}
		if (strcasecmp($viewmode,"list")==0) {
			error_log("Loading List View");
			$recordingsList = file_get_contents('style/recordings_list.html');
		} else {
			error_log("Loading Tile View");
			$recordingsList = file_get_contents('style/recordings_tiles.html');
		}
		$revealContent = file_get_contents('style/recordingdeletereveal.html');
		$revealContent =  str_replace('<!-- dvr_series_id -->',$seriesid,$revealContent);
		$recordingsList = str_replace('<!-- dvr_recordings_count -->','Found: ' . $numRecordings . ' Recordings',$recordingsList);
		$recordingsList = str_replace('<!-- dvr_recordings_list -->',$recordingsData,$recordingsList);
		$recordingsList .= $revealContent;	
		return $recordingsList;
	}
	
?>

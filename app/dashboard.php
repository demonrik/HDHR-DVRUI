<?php
	require_once("TinyAjaxBehavior.php");
	require_once("vars.php");
	require_once("includes/dvrui_hdhrjson.php");
	require_once("includes/dvrui_rules.php");
	require_once("includes/dvrui_recordings.php");
	require_once("includes/dvrui_upcoming.php");


	function openDashboardPage() {
		// prep
		ob_start();
		$tab = new TinyAjaxBehavior();

		//create output
		$htmlStr = buildDashboard();

		//get data
		$result = ob_get_contents();
		ob_end_clean();

		//display
		$tab->add(TabInnerHtml::getBehavior("dashboard_box", $htmlStr));

		return $tab->getString();
	}
	
	function buildDashboard() {
		$dashboard = '';

		$hdhr = new DVRUI_HDHRjson();
		$hdhrRules = new DVRUI_Rules($hdhr);
		$hdhrRules->processAllRules();
		$hdhrRecordings = new DVRUI_Recordings($hdhr);
		$hdhrRecordings->processAllRecordings($hdhr);
		$hdhrRecordings->sortRecordings('DD');


		$hardware = file_get_contents('style/dashboard_hardware.html');
		$hardware = str_replace('<!-- dvrui_hdhrlist_data -->',getDashboardHardware($hdhr),$hardware);
		$recent = file_get_contents('style/dashboard_recent.html');
		$recent = str_replace('<!-- dvr_recent_recordings_list -->',getRecentRecordings($hdhr,$hdhrRecordings),$recent);
		$soon = file_get_contents('style/dashboard_upcoming.html');
		$soon = str_replace('<!-- dvr_soon_list -->',getSoon($hdhr,$hdhrRecordings,$hdhrRules),$soon);
		
		$dashboard .= $hardware;
		$dashboard .= $recent;
		$dashboard .= $soon;
		
		return $dashboard;
	}
	
	function getDashboardHardware($hdhr) {
		$devices =  $hdhr->device_count();
		$engines =  $hdhr->engine_count();

		$hdhr_data = '';
		for ($i=0; $i < $devices; $i++) {
			$hdhrEntry = file_get_contents('style/hdhrlist_entry.html');
			$hdhr_device_data = "<a href=\"" . $hdhr->get_device_baseurl($i) . "\">" . $hdhr->get_device_id($i) . "</a>";
			$hdhr_lineup_data = "<a href=\"" . $hdhr->get_device_lineup($i) . "\">" . $hdhr->get_device_channels($i) . " Channels</a>";
			$hdhrEntry = str_replace('<!--hdhr_device-->',$hdhr_device_data,$hdhrEntry);
			$hdhrEntry = str_replace('<!--hdhr_channels-->',$hdhr_lineup_data,$hdhrEntry);
			$hdhrEntry = str_replace('<!--hdhr_model-->',$hdhr->get_device_model($i),$hdhrEntry);
			$hdhrEntry = str_replace('<!--hdhr_tuners-->',$hdhr->get_device_tuners($i) . ' tuners',$hdhrEntry);
			$hdhrEntry = str_replace('<!--hdhr_firmware-->',$hdhr->get_device_firmware($i),$hdhrEntry);
			$hdhrEntry = str_replace('<!--hdhr_legacy-->',$hdhr->get_device_legacy($i),$hdhrEntry);
			$hdhrEntry = str_replace('<!--hdhr_image-->',$hdhr->get_device_image($i),$hdhrEntry);
			$hdhr_data .= $hdhrEntry ;
		}
		for ($i=0; $i < $engines; $i++) {
			$engineEntry = file_get_contents('style/recordenginelist_entry.html');
			$engineEntry = str_replace('<!--rec_image-->',$hdhr->get_engine_image($i),$engineEntry);
			$engineEntry = str_replace('<!--rec_name-->',$hdhr->get_engine_name($i),$engineEntry);
			$engineEntry = str_replace('<!--rec_version-->',$hdhr->get_engine_version($i),$engineEntry);
			$engineEntry = str_replace('<!--rec_freespace-->',$hdhr->get_engine_space($i),$engineEntry);
			$engine_discover_data = "<a href=\"" . $hdhr->get_engine_discoverURL($i) . "\">" . $hdhr->get_engine_local_ip($i) . "</a>";
			$engineEntry = str_replace('<!--rec_localip-->',$engine_discover_data,$engineEntry);
			$hdhr_data .= $engineEntry;
		}
		return $hdhr_data;
	}

	function getRecentRecordings($hdhr, $hdhrRecordings) {
		
		$numRecordings = $hdhrRecordings->getRecordingCount();
		if ($numRecordings > 10) {
			$numRecordings = 10;
		}
		
		$recordingsData = '';
		for ($i=0; $i < $numRecordings; $i++) {
			$recordingsEntry = file_get_contents('style/recordings_entry_nodecorators.html');
			$recordingsEntry = str_replace('<!-- dvr_recordings_id -->',$hdhrRecordings->getRecordingID($i),$recordingsEntry);
			$recordingsEntry = str_replace('<!-- dvr_series_id -->',$hdhrRecordings->getSeriesID($i),$recordingsEntry);
			$recordingsEntry = str_replace('<!-- dvr_recordings_image -->',$hdhrRecordings->getRecordingImage($i),$recordingsEntry);
			$recordingsEntry = str_replace('<!-- dvr_recordings_episode -->',$hdhrRecordings->getEpisodeNumber($i),$recordingsEntry);
			$recordingsEntry = str_replace('<!-- dvr_recordings_show -->',$hdhrRecordings->getEpisodeTitle($i),$recordingsEntry);
			$recordingsEntry = str_replace('<!-- dvr_recordings_title -->',$hdhrRecordings->getTitle($i),$recordingsEntry);
			$recordingsEntry = str_replace('<!-- dvr_recordings_start -->',$hdhrRecordings->getShortStartTime($i),$recordingsEntry);
			$recordingsEntry = str_replace('<!-- dvr_recordings_originaldate -->',$hdhrRecordings->getShortOriginalAirDate($i),$recordingsEntry);
			$recordingsEntry = str_replace('<!-- dvr_recordings_chname -->',$hdhrRecordings->getChannelName($i),$recordingsEntry);
			$recordingsEntry = str_replace('<!-- dvr_recordings_chnumber -->',$hdhrRecordings->getChannelNumber($i),$recordingsEntry);
			$recordingsEntry = str_replace('<!-- dvr_recordings_chaffiliate -->',$hdhrRecordings->getChannelAffiliate($i),$recordingsEntry);
			$recordingsData .= $recordingsEntry;
		}
		
		return $recordingsData;
	}

	function getSoon($hdhr, $hdhrRecordings, $hdhrRules) {
		$soonStr = '';

		$upcoming = new DVRUI_Upcoming($hdhr,$hdhrRecordings);
		$upcoming->initByRules($hdhrRules);
		$numRules = $upcoming->getSeriesCount();
		for ($i=0; $i < $numRules; $i++) {
			$upcoming->processNext($i); 
		}
		
		$upcoming->sortUpcomingByDate();
		$numShows = $upcoming->getUpcomingCount();
		if ($numShows > 10) {
			$numShows = 10;
		}
		
		for ($i=0;$i < $numShows;$i++) {
			$entry = file_get_contents('style/upcoming_entry.html');
			$entry = str_replace('<!-- dvr_upcoming_title -->',$upcoming->getTitle($i),$entry);
			$entry = str_replace('<!-- dvr_upcoming_episode -->',$upcoming->getEpNum($i) . ' : ' . $upcoming->getEpTitle($i),$entry);
			$entry = str_replace('<!-- dvr_upcoming_original_airdate -->',$upcoming->getEpOriginalAirDate($i),$entry);
			$entry = str_replace('<!-- dvr_upcoming_image -->',$upcoming->getEpImg($i),$entry);
			$entry = str_replace('<!-- dvr_upcoming_start -->',$upcoming->getEpStart($i),$entry);
			$entry = str_replace('<!-- dvr_upcoming_channels -->',$upcoming->getEpChannelNum($i),$entry);
			$entry = str_replace('<!-- dvr_upcoming_channel_name -->',$upcoming->getEpChannelName($i),$entry);
			$soonStr .= $entry;
		}
		
		
		return $soonStr;
	}

?>
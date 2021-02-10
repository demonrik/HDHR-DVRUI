<?php
require_once("common/common.php");
require_once("dvr_api/dvr_discovery.php");
require_once("dvr_api/dvr_engine.php");
require_once("dvr_api/dvr_series.php");
require_once("dvr_api/dvr_episode.php");

function openRecordingsPage() {
	// prep
	ob_start();
	$tab = new TinyAjaxBehavior();

	//create output
	$htmlStr=build_recordings_page();

	//get data
	$result = ob_get_contents();
	ob_end_clean();

	//display
	$tab->add(TabInnerHtml::getBehavior("recordings_box", $htmlStr));
	return $tab->getString();
}

function build_recordings_page() {
	$htmlStr = '';
	$engines = file_get_contents('style/recordings_engines.html');
	$series = file_get_contents('style/recordings_series.html');

	$engineList = get_engine_list();

	$engines = str_replace('<!-- dvrui_recordings_engines_data -->',get_engine_list(),$engines);
	$series = str_replace('<!-- dvrui_recordings_series_list -->','SeriesList',$series);	
	$series = str_replace('<!-- dvrui_recordings_series_info -->','SeriesInfo',$series);	
	$series = str_replace('<!-- dvrui_recordings_series_episodes -->','EpisodeList',$series);	

	$htmlStr .= $engines;
	$htmlStr .= $series;
	
	return $htmlStr;
}

function get_engine_list() {
	$htmlStr = '';
	$discovery = new dvr_discovery();
	for ($i=0;$i<$discovery->device_count();$i++) {
		if (!$discovery->is_legacy_device($i)) {
			if ($discovery->is_recorder($i)){
				error_log("Adding Engine to Recordings/Engine List");
				$engine = new dvr_engine($discovery->get_discover_url($i));
				$engine_entry = file_get_contents('style/recordings_engines_entry.html');
				$engine_entry = str_replace('<!-- dvr_recordings_engine_image -->',$engine->get_image_url(),$engine_entry);	
				$engine_entry = str_replace('<!-- dvr_recordings_engine_id -->',$discovery->get_local_ip($i),$engine_entry);	
				$htmlStr.= $engine_entry;
			}
		}
	}
	return $htmlStr;
}

function getSeries($engines) {
	// prep
	ob_start();
	$tab = new TinyAjaxBehavior();

	//create output
	$htmlStr = get_series_list(json_decode($engines));

	//get data
	$result = ob_get_contents();
	ob_end_clean();

	//display
	$tab->add(TabInnerHtml::getBehavior("recordings_series_list", $htmlStr));
	return $tab->getString();
}


function get_series_list($engine_ips) {
	$htmlStr='';
	$discovery = new dvr_discovery();
	for ($i=0;$i<$discovery->device_count();$i++) {
		for ($j=0; $j < count($engine_ips); $j++) {
			if ($discovery->get_local_ip($i) == $engine_ips[$j]) {
				error_log('Getting Series list for ' . $engine_ips[$j]);
				$engine = new dvr_engine($discovery->get_discover_url($i));
				$json = getCachedJsonFromUrl($engine->get_storage_url());
				error_log('Got ['. print_r($json,true) . ']');
				for ($k = 0; $k < count ($json) ; $k++) {
					$series = new dvr_series($json[$k]);
					$series_entry = file_get_contents('style/series_entry_tile.html');
					$series_entry = str_replace('<!-- dvr_recordings_image -->',$series->get_image_url(),$series_entry);	
					$series_entry = str_replace('<!-- dvr_series_id -->',$series->get_series_id(),$series_entry);	
					$series_entry = str_replace('<!-- dvr_series_engine -->',$engine_ips[$j],$series_entry);	
					$series_entry = str_replace('<!-- dvr_recordings_title -->',$series->get_title(),$series_entry);	
					$series_entry = str_replace('<!-- dvr_rules_count -->',$series->get_episodes_count(),$series_entry);
					$htmlStr .= $series_entry . '<br/>';
				}
			}
		}
	}
	return $htmlStr;
}

function getSeriesInfo($seriesInf){
	// prep
	ob_start();
	$tab = new TinyAjaxBehavior();

	//create output
	$seriesI = json_decode($seriesInf);
	$infoStr = buildSeriesInfo($seriesI[0], $seriesI[1]);

	//get data
	$result = ob_get_contents();
	ob_end_clean();

	//display
	$tab->add(TabInnerHtml::getBehavior("recordings_series_info", $infoStr[0]));
	$tab->add(TabInnerHtml::getBehavior("recordings_series_episodes", $infoStr[1]));
	return $tab->getString();
}

function buildSeriesInfo($seriesID, $engineIP) {
	$outStr=[];
	$infStr = '';
	$epStr = '';
	$discovery = new dvr_discovery();
	for ($i=0;$i<$discovery->device_count();$i++) {
		if ($discovery->get_local_ip($i) == $engineIP) {
			error_log('Getting Series Info for ' . $seriesID . ' on ' . $engineIP);
			$engine = new dvr_engine($discovery->get_discover_url($i));
			$json = getCachedJsonFromUrl($engine->get_storage_url());
			for ($k = 0; $k < count ($json) ; $k++) {
				$series = new dvr_series($json[$k]);
				if ($seriesID == $series->get_series_id()) {
					error_log('Matched Series ID ' . $seriesID);
					$series_entry = file_get_contents('style/series_info.html');
					$series_entry = str_replace('<!-- dvr_series_image -->',$series->get_image_url(),$series_entry);	
					$series_entry = str_replace('<!-- dvr_series_name -->',$series->get_title(),$series_entry);	
					$series_entry = str_replace('<!-- dvr_series_epcount -->',$series->get_episodes_count(),$series_entry);
					$series_entry = str_replace('<!-- dvr_series_category -->',$series->get_category(),$series_entry);
					$series_entry = str_replace('<!-- dvr_series_started -->',$series->get_start_time(),$series_entry);
					$series_entry = str_replace('<!-- dvr_11111111111111series_id -->',$series->get_series_id(),$series_entry);
					$infStr .= $series_entry;
					$eplist = getCachedJsonFromUrl($series->get_episodes_url());
					for ($j = 0 ; $j < count($eplist); $j++) {
						error_log('Processing Episode info for ' . $seriesID);
						$episode = new dvr_episode($eplist[$j]);
						$episode_entry = file_get_contents('style/recordings_entry_list.html');
						$episode_entry = str_replace('<!-- dvr_recordings_id -->',$episode->get_program_id(),$episode_entry);
						$episode_entry = str_replace('<!-- dvr_recordings_title -->',$episode->get_ep_title(),$episode_entry);
						$episode_entry = str_replace('<!-- dvr_recordings_image -->',$episode->get_image_url(),$episode_entry);
						$episode_entry = str_replace('<!-- dvr_recordings_episode -->',$episode->get_ep_number(),$episode_entry);
						$episode_entry = str_replace('<!-- dvr_recordings_synopsis -->',$episode->get_synopsis(),$episode_entry);
						$epStr .= $episode_entry;
					}
					break;
				}
			}
		}
	}
	$outStr[0] = $infStr;
	$outStr[1] = $epStr;
	return $outStr;
}

?>
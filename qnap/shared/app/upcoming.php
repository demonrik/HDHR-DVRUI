<?php
	require_once("TinyAjaxBehavior.php");
	require_once("vars.php");
	require_once("includes/dvrui_hdhrjson.php");
	require_once("includes/dvrui_rules.php");
	require_once("includes/dvrui_upcoming.php");
	
	$upcoming_list_pos = 0;
	$upcoming;
	
	function openUpcomingPage($seriesid) {
		// prep
		ob_start();
		$tab = new TinyAjaxBehavior();
		//create output
		if(strlen($seriesid) > 3){
			$htmlStr = getSeriesUpcoming($seriesid);
		}else{
			$htmlStr = getUpcoming();
		}
		//get data
		$result = ob_get_contents();
		ob_end_clean();
	
		//display
		$tab->add(TabInnerHtml::getBehavior("upcoming_box", $htmlStr));
		return $tab->getString();
	}

	function getUpcoming() {
		// Discover Recording Rules
		$htmlStr = '';
		$entryData = '';
		$hdhr = new DVRUI_HDHRjson();
		$hdhrRules = new DVRUI_Rules($hdhr);
		$hdhrRules->processAllRules();
		$hdhrRecordings = new DVRUI_Recordings($hdhr);
		$hdhrRecordings->processAllRecordings($hdhr);
		$upcoming = new DVRUI_Upcoming($hdhr,$hdhrRecordings);
		$upcoming->initByRules($hdhrRules);
		$numRules = $upcoming->getSeriesCount();
		for ($i=0; $i < $numRules; $i++) {
			$upcoming->processNext($i); 
		}
		
		$upcoming->sortUpcomingByDate();
		$numShows = $upcoming->getUpcomingCount();
		$prevdate = "";
		for ($i=0; $i < $numShows; $i++) {
			$newdate = $upcoming->getEpStartShort($i);
			if($prevdate != $newdate){
				$entry = file_get_contents('style/upcoming_datebreak.html');
				$entry = str_replace('<!-- dvr_upcoming_date -->',$newdate,$entry);
				$entryData .= $entry;
			}

			$entry = file_get_contents('style/upcoming_entry.html');
			$entry = str_replace('<!-- dvr_upcoming_title -->',$upcoming->getTitle($i),$entry);
			$entry = str_replace('<!-- dvr_upcoming_episode -->',$upcoming->getEpNum($i) . ' : ' . $upcoming->getEpTitle($i),$entry);
			$entry = str_replace('<!-- dvr_upcoming_original_airdate -->',$upcoming->getEpOriginalAirDate($i),$entry);
			$entry = str_replace('<!-- dvr_upcoming_image -->',$upcoming->getEpImg($i),$entry);
			//$entry = str_replace('<!-- dvr_upcoming_synopsis -->',$upcoming->getEpSynopsis($i),$entry);
			$entry = str_replace('<!-- dvr_upcoming_start -->',$upcoming->getEpStart($i),$entry);
			$entry = str_replace('<!-- dvr_upcoming_stop -->',$upcoming->getEpEnd($i),$entry);
			$entry = str_replace('<!-- dvr_upcoming_channels -->',$upcoming->getEpChannelNum($i),$entry);
			$entry = str_replace('<!-- dvr_upcoming_channel_name -->',$upcoming->getEpChannelName($i),$entry);
			$entryData .= $entry;
			$prevdate = $newdate;
		}
		$htmlStr = getCalendar($upcoming);
		$htmlStr .= file_get_contents('style/upcoming_list.html');

		$htmlStr = str_replace('<!-- dvr_upcoming_count -->','Found: ' . $numShows . ' Shows. ',$htmlStr);
		$htmlStr = str_replace('<!-- dvr_upcoming_list -->',$entryData,$htmlStr);
		
		
		return $htmlStr;
	}

	function getSeriesUpcoming($seriesid) {
		$htmlStr = '';
		$entryData = '';
		$hdhr = new DVRUI_HDHRjson();
		$hdhrRecordings = new DVRUI_Recordings($hdhr);
		$hdhrRecordings->processAllRecordings($hdhr);
		$upcoming = new DVRUI_Upcoming($hdhr,$hdhrRecordings);
		$upcoming->initBySeries($seriesid);
		
		$upcoming->sortUpcomingByDate();
		$numShows = $upcoming->getUpcomingCount();
		$prevdate = "";
		for ($i=0; $i < $numShows; $i++) {
			$newdate = $upcoming->getEpStartShort($i);
			if($prevdate != $newdate){
				$entry = file_get_contents('style/upcoming_datebreak.html');
				$entry = str_replace('<!-- dvr_upcoming_date -->',$newdate,$entry);
				$entryData .= $entry;
			}
			$entry = file_get_contents('style/upcoming_entry.html');
			$entry = str_replace('<!-- dvr_upcoming_title -->',$upcoming->getTitle($i),$entry);
			$entry = str_replace('<!-- dvr_upcoming_episode -->',$upcoming->getEpNum($i) . ' : ' . $upcoming->getEpTitle($i),$entry);
			$entry = str_replace('<!-- dvr_upcoming_original_airdate -->',$upcoming->getEpOriginalAirDate($i),$entry);
			$entry = str_replace('<!-- dvr_upcoming_image -->',$upcoming->getEpImg($i),$entry);
//			$entry = str_replace('<!-- dvr_upcoming_synopsis -->',$upcoming->getEpSynopsis($i),$entry);
			$entry = str_replace('<!-- dvr_upcoming_start -->',$upcoming->getEpStart($i),$entry);
			$entry = str_replace('<!-- dvr_upcoming_stop -->',$upcoming->getEpEnd($i),$entry);
			$entry = str_replace('<!-- dvr_upcoming_channels -->',$upcoming->getEpChannelNum($i),$entry);
			$entry = str_replace('<!-- dvr_upcoming_channel_name -->',$upcoming->getEpChannelName($i),$entry);
			$entryData .= $entry;
			$prevdate = $newdate;
		}

		$htmlStr = getCalendar($upcoming);
		$htmlStr .= file_get_contents('style/upcoming_list.html');
		$htmlStr = str_replace('<!-- dvr_upcoming_count -->','Found: ' . $numShows . ' Shows. ',$htmlStr);
		$htmlStr = str_replace('<!-- dvr_upcoming_list -->',$entryData,$htmlStr);
		
		
		return $htmlStr;
	}


	function getCalendar($upcoming) {
		$html = "";
		for($i=0;$i<21;$i++){
 			$interval = 'now +' . $i . ' days';
			$datearr[] = new DateTime($interval);
		}
 

		$html .= "<div class=datepicker><table class=datepicker>";
		$html .= "<tr class=datepicker>";
		for($j=0;$j<21;$j++){
			$html .= "<div class='date'  onClick=\"goto('" . date_format($datearr[$j],'D M d') . "');\">";
			$html .= "<p><span>" . date_format($datearr[$j],'M d') . "</span>";
			$html .= $upcoming->countByDate($datearr[$j]) . "</p></div>";
		}

		$html .= "</tr></table></div>";
		return $html;
	}

?>

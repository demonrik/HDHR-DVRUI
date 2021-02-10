<?php
require_once("common/common.php");
require_once("dvr_api/dvr_discovery.php");
require_once("dvr_api/dvr_engine.php");
require_once("dvr_api/dvr_series.php");
require_once("dvr_api/dvr_episode.php");
require_once("dvr_api/dvr_rules.php");

function openRulesPage() {
	// prep
	ob_start();
	$tab = new TinyAjaxBehavior();

	//create output
	$htmlStr=getRecordingRules();

	//get data
	$result = ob_get_contents();
	ob_end_clean();

	//display
	$tab->add(TabInnerHtml::getBehavior("recordings_box", $htmlStr));
	return $tab->getString();
}

function getRecordingRules() {
	$rulesStr = '';
	$auth='';
	$discovery = new dvr_discovery();
	for ($i=0; $i < $discovery->device_count(); $i++) {
		if ((!$discovery->is_legacy_device($i)) && ($discovery->is_tuner($i))) {
			$tuner = new dvr_tuner($discovery->get_discover_url($i));
			$auth .= $tuner->get_device_auth();
		}
	}
	$rules = new dvr_rules($auth);
	
	/* Discover Recording Rules
	$hdhr = new DVRUI_HDHRjson();
	$hdhrRules = new DVRUI_Rules($hdhr);
	$hdhrRecordings = new DVRUI_Recordings($hdhr);
	if(strlen($seriesid) > 3){
		$hdhrRules->processRuleforSeries($seriesid);
	}else{	
		$hdhrRules->processAllRules();
	}	
	$hdhrRecordings->processAllRecordings($hdhr);
	$numRules = $hdhrRules->getRuleCount();
	$rulesData = '';
	for ($i=0; $i < $numRules; $i++) {
		$reccount = $hdhrRecordings->getRecordingCountBySeries($hdhrRules->getRuleSeriesID($i));
		if (strcasecmp($viewmode,"list")==0) {
			$rulesEntry = file_get_contents('style/rules_entry_list.html');
		} else {
			$rulesEntry = file_get_contents('style/rules_entry_tile.html');
		}
	
		$rulesEntry = str_replace('<!-- dvr_rules_id -->',$hdhrRules->getRuleRecID($i) ,$rulesEntry);
		if (URLExists($hdhrRules->getRuleImage($i))) {
			$rulesEntry = str_replace('<!-- dvr_rules_image -->',$hdhrRules->getRuleImage($i),$rulesEntry);
		} else {
			$rulesEntry = str_replace('<!-- dvr_rules_image -->',NO_IMAGE,$rulesEntry);
		}
		
		$rulesEntry = str_replace('<!-- dvr_rules_priority -->',$hdhrRules->getRulePriority($i),$rulesEntry);
		$rulesEntry = str_replace('<!-- dvr_rules_priorityPlus -->',$i-2,$rulesEntry);
		$rulesEntry = str_replace('<!-- dvr_rules_priorityMinus -->',$i+1,$rulesEntry);
		$rulesEntry = str_replace('<!-- dvr_rules_title -->',$hdhrRules->getRuleTitle($i),$rulesEntry);
		$rulesEntry = str_replace('<!-- dvr_rules_synopsis -->',$hdhrRules->getRuleSynopsis($i),$rulesEntry);
		$rulesEntry = str_replace('<!-- dvr_rules_startpad -->',$hdhrRules->getRuleStartPad($i),$rulesEntry);
		$rulesEntry = str_replace('<!-- dvr_rules_endpad -->',$hdhrRules->getRuleEndPad($i),$rulesEntry);
		$rulesEntry = str_replace('<!-- dvr_rules_channels -->',$hdhrRules->getRuleChannels($i),$rulesEntry);
		$rulesEntry = str_replace('<!-- dvr_reccount -->',$reccount,$rulesEntry);
		$rulesEntry = str_replace('<!-- dvr_rules_recent -->',$hdhrRules->getRuleRecent($i),$rulesEntry);
		$rulesEntry = str_replace('<!-- dvr_series_id -->',$hdhrRules->getRuleSeriesID($i),$rulesEntry);
		if(strlen($hdhrRules->getRuleAfterAirDate($i)) > 5 ){
			$rulesEntry = str_replace('<!-- dvr_rules_airdate -->',", After Original Airdate: " . $hdhrRules->getRuleAfterAirDate($i),$rulesEntry);
		}
		if(strlen($hdhrRules->getRuleDateTime($i)) > 5 ){
			$rulesEntry = str_replace('<!-- dvr_rules_datetime -->',", Record Time: " . $hdhrRules->getRuleDateTime($i),$rulesEntry);
		}
	
		// get upcoming count	
		$upcoming = new DVRUI_Upcoming($hdhr, NULL);
		$upcoming->initBySeries($hdhrRules->getRuleSeriesID($i));
		$upcomingcount = $upcoming->getUpcomingCount();
			if($upcomingcount == 0){
			$upcomingcount = "no upcoming";
		}else{
			$upcomingcount = $upcomingcount . " upcoming";
		}
		$rulesEntry = str_replace('<!-- dvr_series_upcoming -->',$upcomingcount,$rulesEntry);

			$rulesData .= $rulesEntry;
	}

	$rulesList = file_get_contents('style/rules_list.html');
		
	$rulesList = str_replace('<!-- dvr_rules_count -->','Found: ' . $numRules . ' Rules.  ',$rulesList);
	$rulesList = str_replace('<!-- dvr_rules_list -->',$rulesData,$rulesList);
	$rulesList .= file_get_contents('style/ruledeletereveal.html');
	*/
	return $rulesStr;
}


?>
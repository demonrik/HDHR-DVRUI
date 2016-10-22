<?php
	require_once("TinyAjaxBehavior.php");
	require_once("vars.php");
	require_once("statusmessage.php");
	require_once("includes/dvrui_hdhrjson.php");
	require_once("includes/dvrui_rules.php");
	require_once("includes/dvrui_recordings.php");
	require_once("includes/dvrui_upcoming.php");
	
	function openRulesPage($seriesid) {
		// prep
		ob_start();
		$tab = new TinyAjaxBehavior();

		//create output
		$htmlStr = getRecordingRules($seriesid);

		//get data
		$result = ob_get_contents();
		ob_end_clean();

		//display
		$tab->add(TabInnerHtml::getBehavior("rules_box", $htmlStr));

		return $tab->getString();
	}

	function deleteRuleByID($id){
		// prep
		ob_start();
		$tab = new TinyAjaxBehavior();
		
		// delete the rule
		$hdhr = new DVRUI_HDHRjson();
		$hdhrRules = new DVRUI_Rules($hdhr);
		$hdhrRules->deleteRule($id);
	
		// poke each record engine
		$engines =  $hdhr->engine_count();
		for ($i=0; $i < $engines; $i++) {
			$hdhr->poke_engine($i);
		}

		//create output
		$htmlStr = getRecordingRules("");

		//get data	
		$result = ob_get_contents();
		ob_end_clean();

		//display
		$tab->add(TabInnerHtml::getBehavior("rules_box", $htmlStr));
		return $tab->getString();
	}

	function createRule($seriesid, $recentonly, $start, $end, $channel, $recordtime, $recordafter){
		// prep
		ob_start();
		$tab = new TinyAjaxBehavior();

		// poke each record engine
		$engines =  $hdhr->engine_count();
		for ($i=0; $i < $engines; $i++) {
			$hdhr->poke_engine($i);
		}

		//create output
		$htmlStr = getRecordingRules("");

		//get data	
		$result = ob_get_contents();
		ob_end_clean();
	
		//display
		$tab->add(TabInnerHtml::getBehavior("rules_box", $htmlStr));
		return $tab->getString();
	} 

	function getRecordingRules($seriesid) {
		$rulesStr = '';
		
		// Discover Recording Rules
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
			$rulesEntry = file_get_contents('style/rules_entry.html');
			$rulesEntry = str_replace('<!-- dvr_rules_id -->',$hdhrRules->getRuleRecID($i) ,$rulesEntry);
			$rulesEntry = str_replace('<!-- dvr_rules_image -->',$hdhrRules->getRuleImage($i),$rulesEntry);
			$rulesEntry = str_replace('<!-- dvr_rules_priority -->',$hdhrRules->getRulePriority($i),$rulesEntry);
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
			$upcoming = new DVRUI_Upcoming($hdhr);
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
		return $rulesList;
	}
?>

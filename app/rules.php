<?php
	require_once("TinyAjaxBehavior.php");
	require_once("vars.php");
	require_once("statusmessage.php");
	require_once("includes/dvrui_hdhrjson.php");
	require_once("includes/dvrui_rules.php");
	require_once("includes/dvrui_recordings.php");
	
	function openRulesPage($seriesid) {
		// prep
		ob_start();
		$tab = new TinyAjaxBehavior();

		//create output
		$htmlStr = getRecordingRules($seriesid);

		//get data
		$result = ob_get_contents();
		ob_end_clean();

		// get latest status	
		$statusmsg = getLatestHDHRStatus();
	
		//display
		$tab->add(TabInnerHtml::getBehavior("rules_box", $htmlStr));
		if ($result != '' && $result != NULL)
			$tab->add(TabInnerHtml::getBehavior("statusMessage", $result));
		else
			$tab->add(TabInnerHtml::getBehavior("statusMessage", $statusmsg));
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
	
		// get latest status	
		$statusmsg = getLatestHDHRStatus();
	
		//display
		$tab->add(TabInnerHtml::getBehavior("rules_box", $htmlStr));
		if ($result != '' && $result != NULL)
			$tab->add(TabInnerHtml::getBehavior("statusMessage", $result));
		else
			$tab->add(TabInnerHtml::getBehavior("statusMessage", $statusmsg));
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
	
		// get latest status	
		$statusmsg = getLatestHDHRStatus();
	
		//display
		$tab->add(TabInnerHtml::getBehavior("rules_box", $htmlStr));
		if ($result != '' && $result != NULL)
			$tab->add(TabInnerHtml::getBehavior("statusMessage", $result));
		else
			$tab->add(TabInnerHtml::getBehavior("statusMessage", $statusmsg));
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
			if(strlen($hdhrRules->getRuleAfterAirDate($i)) > 5 ){
				$rulesEntry = str_replace('<!-- dvr_rules_airdate -->',", After Original Airdate: " . $hdhrRules->getRuleAfterAirDate($i),$rulesEntry);
			}
			if(strlen($hdhrRules->getRuleDateTime($i)) > 5 ){
				$rulesEntry = str_replace('<!-- dvr_rules_datetime -->',", Record Time: " . $hdhrRules->getRuleDateTime($i),$rulesEntry);
			}

			$rulesData .= $rulesEntry;
		}

		$authRevealID = 'RulesAuth';
		$rulesList = file_get_contents('style/rules_list.html');
		$authReveal = file_get_contents('style/reveal.html');
		$authReveal = str_replace('<!-- drvui_reveal_title -->', 'AuthKey Used:',$authReveal);
		$authReveal = str_replace('<!-- drvui_reveal_content -->', $hdhrRules->getAuth(),$authReveal);

		$rulesList = str_replace('<!-- dvrui_auth_reveal -->',$authRevealID,$rulesList);
		$authReveal = str_replace('<!-- dvrui_reveal -->',$authRevealID,$authReveal);
		
		$rulesList = str_replace('<!-- dvr_rules_count -->','Found: ' . $numRules . ' Rules.  ',$rulesList);
		$rulesList = str_replace('<!-- dvr_rules_list -->',$rulesData,$rulesList);
		$rulesList .= $authReveal;
		$rulesList .= file_get_contents('style/ruledeletereveal.html');
		return $rulesList;
	}
?>

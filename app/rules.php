<?php
	require_once("TinyAjaxBehavior.php");
	require_once("vars.php");
	require_once("statusmessage.php");
	require_once("includes/dvrui_hdhrjson.php");
	require_once("includes/dvrui_rules.php");
	
	function openRulesPage() {
		// prep
		ob_start();
		$tab = new TinyAjaxBehavior();

		//create output
		$htmlStr = getRecordingRules();

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

	function getRecordingRules() {
		$rulesStr = '';
		
		// Discover Recording Rules
		$hdhr = new DVRUI_HDHRjson();
		$hdhrRules = new DVRUI_Rules($hdhr);
		$hdhrRules->processAllRules();
		$numRules = $hdhrRules->getRuleCount();
		$rulesData = '';
		for ($i=0; $i < $numRules; $i++) {
			$rulesEntry = file_get_contents('style/rules_entry.html');
			$rulesEntry = str_replace('<!-- dvr_rules_id -->', 'Rule ' . $i,$rulesEntry);
			$rulesEntry = str_replace('<!-- dvr_rules_image -->',$hdhrRules->getRuleImage($i),$rulesEntry);
			$rulesEntry = str_replace('<!-- dvr_rules_priority -->',$hdhrRules->getRulePriority($i),$rulesEntry);
			$rulesEntry = str_replace('<!-- dvr_rules_title -->',$hdhrRules->getRuleTitle($i),$rulesEntry);
			$rulesEntry = str_replace('<!-- dvr_rules_synopsis -->',$hdhrRules->getRuleSynopsis($i),$rulesEntry);
			$rulesEntry = str_replace('<!-- dvr_rules_startpad -->',$hdhrRules->getRuleStartPad($i),$rulesEntry);
			$rulesEntry = str_replace('<!-- dvr_rules_endpad -->',$hdhrRules->getRuleEndPad($i),$rulesEntry);
			$rulesEntry = str_replace('<!-- dvr_rules_channels -->',$hdhrRules->getRuleChannels($i),$rulesEntry);
			$rulesEntry = str_replace('<!-- dvr_rules_recent -->',$hdhrRules->getRuleRecent($i),$rulesEntry);
			$rulesEntry = str_replace('<!-- dvr_rules_delete -->',$hdhrRules->getRuleDeleteURL($i),$rulesEntry);
			$rulesData .= $rulesEntry;
		}
		$rulesList = file_get_contents('style/rules_list.html');
		$rulesList = str_replace('<!-- dvr_rules_auth -->','AuthKey Used: ' . $hdhrRules->getAuth() . '<br/>',$rulesList);
		$rulesList = str_replace('<!-- dvr_rules_count -->','Found: ' . $numRules . ' Rules<br/>',$rulesList);
		$rulesList = str_replace('<!-- dvr_rules_list -->',$rulesData,$rulesList);
		return $rulesList;
	}
?>

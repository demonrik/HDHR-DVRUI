<?php
require_once("common/common.php");
require_once("dvr_api/dvr_discovery.php");
require_once("dvr_api/dvr_engine.php");
require_once("dvr_api/dvr_series.php");
require_once("dvr_api/dvr_episode.php");
require_once("dvr_api/dvr_service.php");
require_once("dvr_api/dvr_rule.php");

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
	$tab->add(TabInnerHtml::getBehavior("rules_box", $htmlStr));
	return $tab->getString();
}

function getRecordingRules() {
	error_log('Getting Recording Rules from Silicondust Servers... ');
	$ruleStr = '';
	$auth='';
	$service = new dvr_service();
	$discovery = new dvr_discovery();
	$service->set_auth($discovery->get_auth());


	$rules = $service->getRules();
	$numrules = count($rules);
	for ($i=0; $i < $numrules; $i++) {
		error_log('Processing rule: ' . $i);
		$rule = new dvr_rule($rules[$i]);
		$ruleEntry = file_get_contents('style/rule_entry_list.html');
		$ruleEntry = str_replace('<!-- dvr_rules_id -->',$rule->get_rule_RecID() ,$ruleEntry);
		/*if (URLExists($rule->get_rule_ImageURL())) {
			$ruleEntry = str_replace('<!-- dvr_rules_image -->',$rule->get_rule_ImageURL(),$ruleEntry);
		} else {
			$ruleEntry = str_replace('<!-- dvr_rules_image -->',NO_IMAGE,$ruleEntry);
		}*/
		$ruleEntry = str_replace('<!-- dvr_rules_image -->',$rule->get_rule_ImageURL(),$ruleEntry);
		
		$ruleEntry = str_replace('<!-- dvr_rules_priority -->',$rule->get_rule_Priority(),$ruleEntry);
		$ruleEntry = str_replace('<!-- dvr_rules_priorityPlus -->',$rule->get_rule_Priority() - 1,$ruleEntry);
		$ruleEntry = str_replace('<!-- dvr_rules_priorityMinus -->',$rule->get_rule_Priority() + 1,$ruleEntry);
		$ruleEntry = str_replace('<!-- dvr_rules_title -->',$rule->get_rule_Title(),$ruleEntry);
		$ruleEntry = str_replace('<!-- dvr_rules_synopsis -->',$rule->get_rule_Synopsis(),$ruleEntry);
		$ruleEntry = str_replace('<!-- dvr_rules_startpad -->',$rule->get_rule_StartPad(),$ruleEntry);
		$ruleEntry = str_replace('<!-- dvr_rules_endpad -->',$rule->get_rule_EndPad(),$ruleEntry);
		$ruleEntry = str_replace('<!-- dvr_rules_channels -->',$rule->get_rule_Channel(),$ruleEntry);
		//$rulesEntry = str_replace('<!-- dvr_reccount -->',$reccount,$ruleEntry);
		$ruleEntry = str_replace('<!-- dvr_rules_recent -->',$rule->get_rule_Recent(),$ruleEntry);
		$ruleEntry = str_replace('<!-- dvr_series_id -->',$rule->get_rule_SeriesID(),$ruleEntry);
		if(strlen($rule->get_rule_Airdate()) > 5 ){
			$ruleEntry = str_replace('<!-- dvr_rules_airdate -->',", After Original Airdate: " . $rule->get_rule_Airdate(),$ruleEntry);
		}
		if(strlen($rule->get_rule_DateTimeOnly()) > 5 ){
			$ruleEntry = str_replace('<!-- dvr_rules_datetime -->',", Record Time: " . $rule->get_rule_DateTimeOnly(),$ruleEntry);
		}
		$ruleStr .= $ruleEntry;
	}
	$rulesList = file_get_contents('style/rules_list.html');
	$rulesList = str_replace('<!-- dvr_rules_count -->','Found: ' . $numrules . ' Rules.  ',$rulesList);
	$rulesList = str_replace('<!-- dvr_rules_list -->',$ruleStr,$rulesList);
	$rulesList .= file_get_contents('style/rule_deletereveal.html');

	return $rulesList;
}


?>
<?php
	require_once("TinyAjaxBehavior.php");
	require_once("vars.php");
	require_once("statusmessage.php");
	require_once("includes/dvrui_hdhrjson.php");
	require_once("includes/dvrui_recordings.php");
	require_once("includes/dvrui_rules.php");

	function openSeriesPage() {
		// prep
		ob_start();
		$tab = new TinyAjaxBehavior();

		//create output
		$hdhr = new DVRUI_HDHRjson();
		$hdhrSeries = new DVRUI_Recordings($hdhr);
		$hdhrSeries->processSeriesList($hdhr,"root");
	
		$hdhrRecordings = new DVRUI_Recordings($hdhr);
		$hdhrRecordings->processAllRecordings($hdhr);

		$hdhrRules = new DVRUI_Rules($hdhr);
		$hdhrRules->processAllRules();
		$numSeries = $hdhrSeries->getRecordingCount();
		$htmlStr = processSeriesData($hdhrRules,$hdhrSeries, $hdhrRecordings, $numSeries);
		//get data
		$result = ob_get_contents();
		ob_end_clean();

		// get latest status	
		$statusmsg = getLatestHDHRStatus();
	
		//display
		$tab->add(TabInnerHtml::getBehavior("series_box", $htmlStr));
		if ($result != '' && $result != NULL)
			$tab->add(TabInnerHtml::getBehavior("statusMessage", $result));
		else
			$tab->add(TabInnerHtml::getBehavior("statusMessage", $statusmsg));
		return $tab->getString();
	}


	function processSeriesData($hdhrRules, $hdhrSeries, $hdhrRecordings, $numSeries) {
		$seriesData = '';
		for ($i=0; $i < $numSeries; $i++) {
			$reccount = $hdhrRecordings->getRecordingCountBySeries($hdhrSeries->getSeriesID($i));
			$rulecount = $hdhrRules->getRuleCountBySeries($hdhrSeries->getSeriesID($i));
			if($reccount == 0){
				$reccount = "no recordings";
			}else if($reccount == 1){
				$reccount = "1 recording";
			}else{
				$reccount = $reccount . " recordings";
			}	

			if($rulecount == 0){
				$rulecount = "no rules";
			}else if($rulecount == 1){
				$rulecount = "1 rule";
			}else{
				$rulecount = $rulecount . " rules";
			}

			$seriesEntry = file_get_contents('style/series_entry.html');
			$seriesEntry = str_replace('<!-- dvr_series_id -->',$hdhrSeries->getSeriesID($i),$seriesEntry);
			$seriesEntry = str_replace('<!-- dvr_recordings_image -->',$hdhrSeries->getRecordingImage($i),$seriesEntry);
			$seriesEntry = str_replace('<!-- dvr_recordings_title -->',$hdhrSeries->getTitle($i),$seriesEntry);
			$seriesEntry = str_replace('<!-- dvr_recordings_count -->',$reccount,$seriesEntry);
			$seriesEntry = str_replace('<!-- dvr_rules_count -->',$rulecount,$seriesEntry);


			$seriesData .= $seriesEntry;
		}
		$seriesList = file_get_contents('style/series_list.html');
		$seriesList = str_replace('<!-- dvr_recordings_list -->',$seriesData,$seriesList);
		return $seriesList;
	}
	
?>

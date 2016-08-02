<?php
	require_once("TinyAjaxBehavior.php");
	require_once("vars.php");
	require_once("statusmessage.php");
	require_once("includes/dvrui_hdhrjson.php");
	require_once("includes/dvrui_search.php");
	require_once("includes/dvrui_rules.php");
	
	function openSearchPage($searchString) {
		// prep
		ob_start();
		$tab = new TinyAjaxBehavior();

		//create output
		$htmlStr = getSearchResults($searchString);

		//get data
		$result = ob_get_contents();
		ob_end_clean();

		// get latest status	
		$statusmsg = getLatestHDHRStatus();
	
		//display
		$tab->add(TabInnerHtml::getBehavior("search_box", $htmlStr));
		if ($result != '' && $result != NULL)
			$tab->add(TabInnerHtml::getBehavior("statusMessage", $result));
		else
			$tab->add(TabInnerHtml::getBehavior("statusMessage", $statusmsg));
		return $tab->getString();
	}

	function deleteRuleFromSearch($searchString, $id){
                // prep
                ob_start();
                $tab = new TinyAjaxBehavior();

		// delete the rule
		$hdhr = new DVRUI_HDHRjson();
		$hdhrRules = new DVRUI_Rules($hdhr);
		$hdhrRules->deleteRule($id);	

                // poke each record engine to reload rules from my.hdhomerun.com
                $engines =  $hdhr->engine_count();
                for ($i=0; $i < $engines; $i++) {
                        $hdhr->poke_engine($i);
                }

                //create output
                $htmlStr = getSearchResults($searchString);

                //get data
                $result = ob_get_contents();
                ob_end_clean();

                // get latest status
                $statusmsg = getLatestHDHRStatus();

                //display
                $tab->add(TabInnerHtml::getBehavior("search_box", $htmlStr));
                if ($result != '' && $result != NULL)
                        $tab->add(TabInnerHtml::getBehavior("statusMessage", $result));
                else
                        $tab->add(TabInnerHtml::getBehavior("statusMessage", $statusmsg));
                return $tab->getString();


	}

        function createRuleFromSearch($searchString, $seriesid, $recentonly, $start, $end, $channel, $recordtime, $recordafter){
                // prep
                ob_start();
                $tab = new TinyAjaxBehavior();

		// create the rule
		$hdhr = new DVRUI_HDHRjson();
		$hdhrRules = new DVRUI_Rules($hdhr);
		$hdhrRules->createRule($seriesid, $recentonly, $start, $end, $channel, $recordtime, $recordafter);	

                // poke each record engine to reload rules from my.hdhomerun.com
                $engines =  $hdhr->engine_count();
                for ($i=0; $i < $engines; $i++) {
                        $hdhr->poke_engine($i);
                }

                //create output
                $htmlStr = getSearchResults($searchString);

                //get data
                $result = ob_get_contents();
                ob_end_clean();

                // get latest status
                $statusmsg = getLatestHDHRStatus();

                //display
                $tab->add(TabInnerHtml::getBehavior("search_box", $htmlStr));
                if ($result != '' && $result != NULL)
                        $tab->add(TabInnerHtml::getBehavior("statusMessage", $result));
                else
                        $tab->add(TabInnerHtml::getBehavior("statusMessage", $statusmsg));
                return $tab->getString();

        }


	function getSearchResults($searchString) {
		$searchStr = '';
		$hdhr = new DVRUI_HDHRjson();
		$hdhrSearchResults = new DVRUI_Search($hdhr, $searchString);
		$numResults = $hdhrSearchResults->getSearchResultCount();
		$searchData = '';

		for ($i=0; $i < $numResults; $i++) {
			$searchEntry = file_get_contents('style/search_entry.html');
			$searchEntry = str_replace('<!-- dvr_search_image -->',$hdhrSearchResults->getSearchResultImage($i),$searchEntry);
			$searchEntry = str_replace('<!-- dvr_search_title -->',$hdhrSearchResults->getSearchResultTitle($i),$searchEntry);
			$searchEntry = str_replace('<!-- dvr_search_synopsis -->',$hdhrSearchResults->getSearchResultSynopsis($i),$searchEntry);
			$searchEntry = str_replace('<!-- dvr_search_channelNumber -->',$hdhrSearchResults->getSearchResultChannelNumber($i),$searchEntry);
			$searchEntry = str_replace('<!-- dvr_search_channelName -->',$hdhrSearchResults->getSearchResultChannelName($i),$searchEntry);
			$searchEntry = str_replace('<!-- dvr_search_originalAirDate -->',$hdhrSearchResults->getSearchResultOriginalAirDate($i),$searchEntry);

			if($hdhrSearchResults->getSearchResultRecordingRules($i) == 0){
				$actionLinks = file_get_contents('style/series_actions.html');
				$actionLinks = str_replace('<!-- dvr_record_recent -->',$hdhrSearchResults->getRecordRecentURL($i),$actionLinks);
				$actionLinks = str_replace('<!-- dvr_record_all -->',$hdhrSearchResults->getRecordAllURL($i),$actionLinks);
				$actionLinks = str_replace('<!-- dvr_series_id -->',$hdhrSearchResults->getSearchResultSeriesID($i),$actionLinks);
				$actionLinks = str_replace('<!-- dvr_series_title -->',$hdhrSearchResults->getSearchResultTitleEscaped($i),$actionLinks);
				$searchEntry = str_replace('<!-- dvr_series_action_links -->',$actionLinks,$searchEntry);
			}else{
				$hdhrRules = new DVRUI_Rules($hdhr);
				$hdhrRules->processRuleforSeries($hdhrSearchResults->getSearchResultSeriesID($i));
				$numRules = $hdhrRules->getRuleCount();
	      			for ($j=0; $j < $numRules; $j++) {
					$rulesEntry = file_get_contents('style/rules_entry_small.html');
					$rulesEntry = str_replace('<!-- dvr_rules_priority -->',$hdhrRules->getRulePriority($j),$rulesEntry);
					$rulesEntry = str_replace('<!-- dvr_rules_startpad -->',$hdhrRules->getRuleStartPad($j),$rulesEntry);
					$rulesEntry = str_replace('<!-- dvr_rules_endpad -->',$hdhrRules->getRuleEndPad($j),$rulesEntry);
					$rulesEntry = str_replace('<!-- dvr_rules_channels -->',$hdhrRules->getRuleChannels($j),$rulesEntry);
					$rulesEntry = str_replace('<!-- dvr_rules_recent -->',$hdhrRules->getRuleRecent($j),$rulesEntry);
					$rulesEntry = str_replace('<!-- dvr_rules_ruleid -->',$hdhrRules->getRuleRecID($j),$rulesEntry);
					if(strlen($hdhrRules->getRuleAfterAirDate($j)) > 5 ){
						$rulesEntry = str_replace('<!-- dvr_rules_airdate -->',", After Original Airdate: " . $hdhrRules->getRuleAfterAirDate($j),$rulesEntry);
					}
					if(strlen($hdhrRules->getRuleDateTime($j)) > 5 ){
						$rulesEntry = str_replace('<!-- dvr_rules_datetime -->',", Record Time: " . $hdhrRules->getRuleDateTime($j),$rulesEntry);
					}
					$rulesEntry = str_replace('<!-- dvr_rules_datetime -->',$hdhrRules->getRuleDateTime($j),$rulesEntry);
					$searchEntry = str_replace('<!-- dvr_series_rule_list -->',$rulesEntry,$searchEntry);

				}
			}
			$searchData .= $searchEntry;
		}
		$searchList = file_get_contents('style/search_list.html');
		$searchList .= file_get_contents('style/advancedrule.html');
		$searchList = str_replace('<!-- dvr_search_count -->','Found: ' . $numResults . ' Results<br/>',$searchList);
		$searchList = str_replace('<!-- dvr_search_list -->',$searchData,$searchList);

		
		return $searchList;
	}
?>

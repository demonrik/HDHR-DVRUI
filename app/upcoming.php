<?php
	require_once("TinyAjaxBehavior.php");
	require_once("vars.php");
	require_once("statusmessage.php");
	require_once("includes/dvrui_hdhrjson.php");
	require_once("includes/dvrui_rules.php");
	require_once("includes/dvrui_upcoming.php");
	
	$upcoming_list_pos = 0;
	$upcoming;
	
	function openUpcomingPage() {
		// prep
		ob_start();
		$tab = new TinyAjaxBehavior();

		//create output
		$htmlStr = getUpcoming();

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
		$upcoming = new DVRUI_Upcoming($hdhrRules);
		$numRules = $upcoming->getSeriesCount();
		for ($i=0; $i < $numRules; $i++) {
			$upcoming->processNext($i); 
		}
		
		$upcoming->sortUpcomingByDate();
		$numShows = $upcoming->getUpcomingCount();
		for ($i=0; $i < $numShows; $i++) {
			$entry = file_get_contents('style/upcoming_entry.html');
			$entry = str_replace('<!-- dvr_upcoming_title -->',$upcoming->getTitle($i),$entry);
			$entry = str_replace('<!-- dvr_upcoming_episode -->',$upcoming->getEpNum($i) . ' : ' . $upcoming->getEpTitle($i),$entry);
			$entry = str_replace('<!-- dvr_upcoming_image -->',$upcoming->getEpImg($i),$entry);
			$entry = str_replace('<!-- dvr_upcoming_synopsis -->',$upcoming->getEpSynopsis($i),$entry);
			$entry = str_replace('<!-- dvr_upcoming_start -->',$upcoming->getEpStart($i),$entry);
			$entry = str_replace('<!-- dvr_upcoming_stop -->',$upcoming->getEpEnd($i),$entry);
			$entry = str_replace('<!-- dvr_upcoming_channels -->',$upcoming->getEpChannelNum($i),$entry);
			$entryData .= $entry;
		}

		$authRevealID = 'UpcomingAuth';
		$authReveal = file_get_contents('style/reveal.html');
		$htmlStr = file_get_contents('style/upcoming_list.html');

		$authReveal = str_replace('<!-- dvrui_reveal -->',$authRevealID,$authReveal);
		$authReveal = str_replace('<!-- drvui_reveal_title -->', 'AuthKey Used:',$authReveal);
		$authReveal = str_replace('<!-- drvui_reveal_content -->', $hdhrRules->getAuth(),$authReveal);

		$htmlStr = str_replace('<!-- dvrui_auth_reveal -->',$authRevealID,$htmlStr);

		$htmlStr = str_replace('<!-- dvr_upcoming_count -->','Found: ' . $numShows . ' Shows. ',$htmlStr);
		$htmlStr = str_replace('<!-- dvr_upcoming_list -->',$entryData,$htmlStr);
		
		$htmlStr .= $authReveal;
		
		return $htmlStr;
	}

?>

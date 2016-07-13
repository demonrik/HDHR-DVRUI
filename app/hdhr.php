<?php
	require_once("TinyAjaxBehavior.php");
	require_once("vars.php");
	require_once("statusmessage.php");
	require_once("includes/dvrui_hdhrjson.php");
	
	function openHDHRPage() {
		// prep
		ob_start();
		$tab = new TinyAjaxBehavior();

		//create output
		$htmlStr = getHDHRData();
		
		//get data
		$result = ob_get_contents();
		ob_end_clean();

		// get latest status	
		$statusmsg = getLatestHDHRStatus();
	
		//display
		$tab->add(TabInnerHtml::getBehavior("hdhr_box", $htmlStr));
		if ($result != '' && $result != NULL)
			$tab->add(TabInnerHtml::getBehavior("statusMessage", $result));
		else
			$tab->add(TabInnerHtml::getBehavior("statusMessage", $statusmsg));
		return $tab->getString();
	}

	function getHDHRData() {
		
		// Discover HDHR Devices
		$hdhr = new DVRUI_HDHRjson();
		$devices =  $hdhr->device_count();
		$hdhr_data = '';
		for ($i=0; $i < $devices; $i++) {
			$hdhrEntry = file_get_contents('style/hdhrlist_entry.html');
			$hdhr_device_data = "<a href=" . $hdhr->get_device_baseurl($i) . ">" . $hdhr->get_device_id($i) . "</a>";
			$hdhr_lineup_data = "<a href=" . $hdhr->get_device_lineup($i) . ">" . $hdhr->get_device_channels($i) . " Channels</a>";
			$hdhrEntry = str_replace('<!--hdhr_device-->',$hdhr_device_data,$hdhrEntry);
			$hdhrEntry = str_replace('<!--hdhr_channels-->',$hdhr_lineup_data,$hdhrEntry);
			$hdhrEntry = str_replace('<!--hdhr_model-->',$hdhr->get_device_model($i),$hdhrEntry);
			$hdhrEntry = str_replace('<!--hdhr_tuners-->',$hdhr->get_device_tuners($i) . ' tuners',$hdhrEntry);
			$hdhrEntry = str_replace('<!--hdhr_firmware-->',$hdhr->get_device_firmware($i),$hdhrEntry);
			$hdhrEntry = str_replace('<!--hdhr_image-->',$hdhr->get_device_image($i),$hdhrEntry);
			$hdhr_data .= $hdhrEntry ;	
		}
		$engines =  $hdhr->engine_count();
		$hdhr_data .= $engines . " recording engines found.";	
		for ($i=0; $i < $engines; $i++) {
			$engineEntry = file_get_contents('style/recordenginelist_entry.html');
			$engineEntry = str_replace('<!--rec_image-->',$hdhr->get_engine_image($i),$engineEntry);
			$engineEntry = str_replace('<!--rec_storageid-->',$hdhr->get_engine_storage_id($i),$engineEntry);
			$engineEntry = str_replace('<!--rec_localip-->',$hdhr->get_engine_local_ip($i),$engineEntry);
			$engineEntry = str_replace('<!--rec_storageurl-->',$hdhr->get_engine_storage_url($i),$engineEntry);
			$hdhr_data .= $engineEntry;
		}
		return $hdhr_data;
	}
?>

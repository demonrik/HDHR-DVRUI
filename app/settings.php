<?php
	require_once("TinyAjaxBehavior.php");
	require_once("vars.php");
	require_once("includes/dvrui_hdhrjson.php");
	require_once("includes/dvrui_common.php");

	function openClearCache() {
		clearCache();
		openSettingsPage();

	}
	
	function openSettingsPage() {
		// prep
		ob_start();
		$tab = new TinyAjaxBehavior();

		//create output
		$htmlStr = getHDHRData();
		
		//get data
		$result = ob_get_contents();
		ob_end_clean();

		//display
		$tab->add(TabInnerHtml::getBehavior("settings_box", $htmlStr));
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
			$hdhrEntry = str_replace('<!--hdhr_legacy-->',$hdhr->get_device_legacy($i),$hdhrEntry);
			$hdhrEntry = str_replace('<!--hdhr_image-->',$hdhr->get_device_image($i),$hdhrEntry);
			$hdhr_data .= $hdhrEntry ;	
		}
		$engines =  $hdhr->engine_count();
		$hdhr_data .= $engines . " recording engines found.";	
		for ($i=0; $i < $engines; $i++) {
			$engineEntry = file_get_contents('style/recordenginelist_entry.html');
			$engineEntry = str_replace('<!--rec_image-->',$hdhr->get_engine_image($i),$engineEntry);
			$engineEntry = str_replace('<!--rec_name-->',$hdhr->get_engine_name($i),$engineEntry);
			$engineEntry = str_replace('<!--rec_version-->',$hdhr->get_engine_version($i),$engineEntry);
			$engineEntry = str_replace('<!--rec_freespace-->',$hdhr->get_engine_space($i),$engineEntry);
			$engine_discover_data = "<a href=" . $hdhr->get_engine_discoverURL($i) . ">" . $hdhr->get_engine_local_ip($i) . "</a>";
			$engineEntry = str_replace('<!--rec_localip-->',$engine_discover_data,$engineEntry);
			$engine_storage_data = "<a href=" . $hdhr->get_engine_storage_url($i) . ">" . $hdhr->get_engine_storage_id($i) . "</a>";
			$engineEntry = str_replace('<!--rec_storageid-->',$engine_storage_data,$engineEntry);
			$hdhr_data .= $engineEntry;
		}
		return $hdhr_data;
	}
?>

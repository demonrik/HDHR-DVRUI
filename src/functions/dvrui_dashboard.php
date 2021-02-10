<?php
require_once("includes/TinyAjaxBehavior.php");
require_once("dvr_api/dvr_discovery.php");

function openDashboardPage() {
	// prep
	ob_start();
	$tab = new TinyAjaxBehavior();
	
	//create output
	$htmlStr = build_dashboard();
	
	//get data
	$result = ob_get_contents();
	ob_end_clean();

	//display
	$tab->add(TabInnerHtml::getBehavior("dashboard_box", $htmlStr));
	return $tab->getString();
}


function build_dashboard() {
	$dashboard = '';

	$hardware = process_devices();
//	$recent = file_get_contents('style/dashboard_recent.html');
//	$soon = file_get_contents('style/dashboard_upcoming.html');
		
	$dashboard .= $hardware;
//	$dashboard .= $recent;
//	$dashboard .= $soon;
	
	return $dashboard;
}


function process_devices() {
	error_log('Processing Device List...');
	$htmlStr = file_get_contents('style/dashboard_hardware.html');

	$discovery = new dvr_discovery();
	$device_list = '';			
	error_log('Processing Devices ' . $discovery->device_count());
	for ($i=0;$i<$discovery->device_count();$i++) {

		if (!$discovery->is_legacy_device($i)) {
			error_log('Processing Device...');

			$hdhr_discover_data = "<a href=\"" . $discovery->get_base_url($i) . "\">" . $discovery->get_local_ip($i) . "</a>";

			if ($discovery->is_tuner($i)) {
				$tuner = new dvr_tuner($discovery->get_discover_url($i));
				$hdhr_entry = file_get_contents('style/hdhrlist_entry.html');
				$hdhr_entry = str_replace('<!--hdhr_ip-->',$hdhr_discover_data,$hdhr_entry);
				$hdhr_device_data = "<a href=\"" . $tuner->get_base_url() . "\">" . $tuner->get_device_id() . "</a>";
				$hdhr_lineup_data = "<a href=\"" . $tuner->get_lineup_url() . "\">" . $tuner->get_channel_count() . " Channels</a>";
				$hdhr_name_data = str_replace("HDHomeRun", "HDHomeRun <br>", $tuner->get_model_name());
				$hdhr_entry = str_replace('<!--hdhr_device-->',$hdhr_device_data,$hdhr_entry);
				$hdhr_entry = str_replace('<!--hdhr_channels-->',$hdhr_lineup_data,$hdhr_entry);
				$hdhr_entry = str_replace('<!--hdhr_model-->',$hdhr_name_data,$hdhr_entry);
				$hdhr_entry = str_replace('<!--hdhr_modelnum-->',$tuner->get_model_number(),$hdhr_entry);
				$hdhr_entry = str_replace('<!--hdhr_tuners-->',$tuner->get_tuners() . ' tuners',$hdhr_entry);
				$hdhr_entry = str_replace('<!--hdhr_fwname-->',$tuner->get_firmware_name(),$hdhr_entry);
				$hdhr_entry = str_replace('<!--hdhr_fwver-->',$tuner->get_firmware_version(),$hdhr_entry);
				$hdhr_entry = str_replace('<!--hdhr_image-->',$tuner->get_image_url(),$hdhr_entry);
				$device_list .= $hdhr_entry;
			}
			if ($discovery->is_recorder($i)){
				$engine = new dvr_engine($discovery->get_discover_url($i));
				$engine_entry = file_get_contents('style/recordenginelist_entry.html');
				$engine_entry = str_replace('<!--rec_localip-->',$hdhr_discover_data,$engine_entry);
				$engine_name_data = str_replace("HDHomeRun", "HDHomeRun <br>", $engine->get_model_name());
				$engine_entry = str_replace('<!--rec_image-->',$engine->get_image_url(),$engine_entry);
				$engine_entry = str_replace('<!--rec_name-->',$engine_name_data,$engine_entry);
				$engine_entry = str_replace('<!--rec_version-->',$engine->get_version(),$engine_entry);
				$engine_entry = str_replace('<!--rec_freespace-->',$engine->get_free_space(),$engine_entry);
				$engine_entry = str_replace('<!--rec_space-->',$engine->get_total_space(),$engine_entry);
				$engine_entry = str_replace('<!--rec_storageid-->',$engine->get_storage_id(),$engine_entry);
				$engine_entry = str_replace('<!--rec_episodes-->',$engine->get_recordings_count(),$engine_entry);
				$engine_storage_data = "<a href=\"" . $engine->get_storage_url() . "\">" . $discovery->get_local_ip($i) . "</a>";
				$engine_entry = str_replace('<!--rec_storageURL-->',$engine_storage_data,$engine_entry);
				$device_list .= $engine_entry;
			}
		} else {
			error_log('Skipping Legacy Device');
		}
	}

	return str_replace('<!-- dvrui_hdhrlist_data -->',$device_list,$htmlStr);
}

?>
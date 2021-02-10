<?php
require_once("config/config.php");
require_once("dvr_api/dvr_discovery.php");
require_once("dvr_api/dvr_tuner.php");
require_once("dvr_api/dvr_engine.php");

function getDiagnosticInfo($options) {
	// prep
	ob_start();
	$tab = new TinyAjaxBehavior();
	$opts = json_decode($options);
	//create output
	$htmlStr="";
	foreach ($opts as $option) {
		$htmlStr.= getDiagnostics($option);
	}
	//get data
	$result = ob_get_contents();
	ob_end_clean();

	//display
	$tab->add(TabInnerHtml::getBehavior("diagnostics_log", $htmlStr));
	return $tab->getString();
}

function getDiagnostics($option) {
	$htmlStr="";
	switch ($option) {
		case 'recordings':
			error_log('Processing Recordings Diagnostics...');
			$htmlStr =  "---------- HDHR DVR Recordings -----------------------------------------------------------<br/>";
			$discovery = new dvr_discovery();
			$htmlStr .= "Found " . $discovery->device_count() ." Devices <br/>";
			for ($i=0;$i<$discovery->device_count();$i++) {
				if (!$discovery->is_legacy_device($i)) {
					if ($discovery->is_recorder($i)){
						$engine = new dvr_engine($discovery->get_discover_url($i));
						$htmlStr .= "Found " . $engine->get_series_count() ." Series @ " . $engine->get_storage_url() . "<br/>";
						$json = getCachedJsonFromUrl($engine->get_storage_url());
						error_log('Got ['. print_r($json,true) . ']');
						for ($j = 0; $j < count ($json) ; $j++) {
							$series = new dvr_series($json[$j]);
							$htmlStr .= "Found " . $series->get_episodes_count() . " Episodes for " . $series->get_title() . " @ " . $series->get_episodes_url() . "</br>";
						}
					}
				}
			}
			break;
		case 'rules':
			error_log('Processing Rules Diagnostics...');
			break;
		case 'account':
			error_log('Processing Account Diagnostics...');
			$htmlStr =  "---------- HDHR Account -----------------------------------------------------------<br/>";
			$url = DVRUI_Config::DVRUI_apiurl . 'api/account?DeviceAuth=';
			$auth = '';
			$discovery = new dvr_discovery();
			for ($i=0; $i < $discovery->device_count(); $i++) {
				if ((!$discovery->is_legacy_device($i)) && ($discovery->is_tuner($i))) {
					$tuner = new dvr_tuner($discovery->get_discover_url($i));
					$auth .= $tuner->get_device_auth();
				}
			}
			$htmlStr .= 'Checking Silicon Dust account for Auth:= ' . $auth . '<br/>';
			$htmlStr .= 'Silicon Dust Responded = ' . getUrl($url . $auth) . '<br/>';
			break;
		case 'device':
			error_log('Processing Device Diagnostics...');
			$htmlStr =  "---------- HDHR Devices -----------------------------------------------------------<br/>";
			$discovery = new dvr_discovery();
			$htmlStr .= "Found " . $discovery->device_count() ." Devices <br/>";
			error_log($htmlStr);
			
			for ($i=0;$i<$discovery->device_count();$i++) {

				if (!$discovery->is_legacy_device($i)) {
					error_log('Processing Device');
					$htmlStr .= "<b>[DeviceID: " . $discovery->get_device_id($i) ."]</b><br/>";
					$htmlStr .= "Legacy Device: NO <br/>";
					$htmlStr .= "Device IP: " . $discovery->get_local_ip($i) ."<br/>";
					$htmlStr .= "Discovery URL: " . $discovery->get_discover_url($i) ."<br/>";
					if ($discovery->is_tuner($i)) {
						$tuner = new dvr_tuner($discovery->get_discover_url($i));
						$htmlStr .= "Detected HDHR Tuner <br/>";
						$htmlStr .= "-  Model Name: " . $tuner->get_model_name() ."<br/>";
						$htmlStr .= "-  Model Number: " . $tuner->get_model_number() ."<br/>";
						$htmlStr .= "-  Firmware: " . $tuner->get_firmware_name() ."<br/>";
						$htmlStr .= "-  Firmware Ver: " . $tuner->get_firmware_version() ."<br/>";
						$htmlStr .= "-  Device Auth: " . $tuner->get_device_auth() ."<br/>";
						$htmlStr .= "-  Base URL: " . $tuner->get_base_url() ."<br/>";
						$htmlStr .= "-  Lineup URL: " . $tuner->get_lineup_url() ."<br/>";
						$htmlStr .= "-  Channel Count: " . $tuner->get_channel_count() ."<br/>";
						$htmlStr .= "-  Num# Tuners:  " . $tuner->get_tuners() ."<br/>";
					}
					if ($discovery->is_recorder($i)){
						$engine = new dvr_engine($discovery->get_discover_url($i));
						$htmlStr .= "Detected HDHR DVR <br/>";
						$htmlStr .= "-  Model Name: " . $engine->get_model_name() ."<br/>";
						$htmlStr .= "-  Base URL: " . $engine->get_base_url() ."<br/>";
						$htmlStr .= "-  Version: " . $engine->get_version() ."<br/>";
						$htmlStr .= "-  Storage ID: " . $engine->get_storage_id() ."<br/>";
						$htmlStr .= "-  Storage URL: " . $engine->get_storage_url() ."<br/>";
						$htmlStr .= "-  Total Space: " . $engine->get_total_space() ."<br/>";
						$htmlStr .= "-  Free Space: " . $engine->get_free_space() ."<br/>";
						$htmlStr .= "-  # Series: " . $engine->get_series_count() ."<br/>";
						$htmlStr .= "-  # Recordings: " . $engine->get_recordings_count() ."<br/>";
					}
				} else {
					error_log('Skipping Legacy Device');
				}
			}
			break;
		case 'connectivity':
			error_log('Processing Connectivity Diagnostics...');
			$myhdhrurl = DVRUI_Config::DVRUI_apiurl . 'discover';
			$htmlStr .= "---------- CONNECTIVITY CHECKS --------------------------------------------------<br/>";
			$htmlStr .= 'DVRUI_apiurl = ' . DVRUI_Config::DVRUI_apiurl . "<br/>";
			$htmlStr .= 'Discover URL = ' . $myhdhrurl . "<br/>";
  			$file      = fsockopen (parse_url($myhdhrurl, PHP_URL_HOST), 80, $errno, $errstr, 10);
  			if (! $file) {
  				$htmlStr .= 'Unable to open connection to ' . parse_url($myhdhrurl, PHP_URL_HOST) ;
  				$htmlStr .= '  Failed with error ' . $errno;
  				$htmlStr .= ' -  ' . $errstr . '<br/>';
  			} else {
  				$htmlStr .= 'Ping to ' . parse_url($myhdhrurl, PHP_URL_HOST) . ' success<br/>';
  			}
			if (in_array('curl', get_loaded_extensions())){
				$htmlStr .= 'curl extension = installed <br/>';
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, $myhdhrurl);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($ch, CURLOPT_TIMEOUT, 2);
				curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
				$json = curl_exec($ch);
				curl_close($ch);
				$htmlStr .= 'curl returned = ' . $json . '<br/>';
			} else { 
				$htmlStr .= 'curl extension = not available <br/>';
			}
			$htmlStr .= 'checking alternative connection <br/>';
			$context = stream_context_create(
				array('http' => array(
					'header'=>'Connection: close\r\n',
					'timeout' => 2.0)));
			$json = file_get_contents($myhdhrurl,false,$context);	
			$htmlStr .= 'stream returned = ' . $json . '<br/>';
			break;
		case 'server':
			error_log('Processing Server Diagnostics...');
			$htmlStr = "---------- OS and ENV VARIABLES --------------------------------------------------<br/>";
			$htmlStr .= 'Operating System = ' . php_uname() . "<br/>";
			$htmlStr .= 'HTTP_HOST = ' . getenv('HTTP_HOST') . "<br/>";
			$htmlStr .= 'SERVER_NAME = ' . getenv('SERVER_NAME') . "<br/>";
			$htmlStr .= 'SERVER_ADDR = ' . getenv('SERVER_ADDR') . "<br/>";
			$htmlStr .= 'SERVER_SOFTWARE = ' . getenv('SERVER_SOFTWARE') . "<br/>";
			$htmlStr .= 'SYS TEMP = ' . sys_get_temp_dir() . "<br/>";
			$htmlStr .= "---------- PHP INFO -------------------------------------------------------------<br/>";
			$htmlStr .= 'php version = ' . phpversion() . "<br/>";
			$htmlStr .= 'php.ini file = ' . php_ini_loaded_file() . "<br/>";
			$htmlStr .= 'php.ini date.timezone = ' . ini_get('date.timezone') . "<br/>";
			$htmlStr .= 'date_default_timezone_get() = ' . date_default_timezone_get() . "<br/>";
			$htmlStr .= "---------- DVRUI VARS -----------------------------------------------------------<br/>";
			$htmlStr .= 'DVRUI_DEBUG = ' . DVRUI_Config::DVRUI_DEBUG . "<br/>";
			$htmlStr .= 'DVRUI_name = ' . DVRUI_Config::DVRUI_name . "<br/>";
			$htmlStr .= 'DVRUI_version = ' . DVRUI_Config::DVRUI_version . "<br/>";
			$htmlStr .= 'DVRUI_apiurl = ' . DVRUI_Config::DVRUI_apiurl . "<br/>";
			$htmlStr .= 'DVRUI_tmp = ' . DVRUI_Config::DVRUI_tmp . "<br/>";
			$htmlStr .= 'DVRUI_TZ = ' . DVRUI_Config::DVRUI_TZ . "<br/>";
			$htmlStr .= 'DVRUI_cache = ' . DVRUI_Config::DVRUI_cache . "<br/>";
			$htmlStr .= "---------- PERMISSIONS ----------------------------------------------------------<br/>";
			$htmlStr .= 'style = ' . substr(sprintf('%o', fileperms('style')), -4) . "<br/>";
			$htmlStr .= 'style/style.css = ' . substr(sprintf('%o', fileperms('style/style.css')), -4) . "<br/>";
			$htmlStr .= 'style/m_style.css = ' . substr(sprintf('%o', fileperms('style/m_style.css')), -4) . "<br/>";
			$htmlStr .= DVRUI_Config::DVRUI_tmp . ' = ' . substr(sprintf('%o', fileperms(DVRUI_Config::DVRUI_tmp)), -4) . "<br/>";
			break;
		default:
	}
	return $htmlStr;
}
?>
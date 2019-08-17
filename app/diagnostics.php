<?php
require_once("includes/dvrui_hdhrjson.php");
require_once("includes/dvrui_common.php");
require_once("vars.php");

if (function_exists('opcache_reset')){
	opcache_reset();
}


  function getServerDiag() {
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
  	$htmlStr .= 'DVRUI_DEBUG = ' . DVRUI_Vars::DVRUI_DEBUG . "<br/>";
  	$htmlStr .= 'DVRUI_name = ' . DVRUI_Vars::DVRUI_name . "<br/>";
  	$htmlStr .= 'DVRUI_version = ' . DVRUI_Vars::DVRUI_version . "<br/>";
  	$htmlStr .= 'DVRUI_TZ = ' . DVRUI_Vars::DVRUI_TZ . "<br/>";
		$htmlStr .= 'DVRUI_discover_cache = ' . DVRUI_Vars::DVRUI_discover_cache . "\n";
		$htmlStr .= 'DVRUI_upcoming_cache = ' . DVRUI_Vars::DVRUI_upcoming_cache . "\n";
  	$htmlStr .= "---------- PERMISSIONS ----------------------------------------------------------<br/>";
  	$htmlStr .= 'style = ' . substr(sprintf('%o', fileperms('style')), -4) . "\n";
  	$htmlStr .= 'themes = ' . substr(sprintf('%o', fileperms('themes')), -4) . "\n";
  	$htmlStr .= 'themes/default = ' . substr(sprintf('%o', fileperms('themes/default')), -4) . "\n";
  	$htmlStr .= 'themes/default/style.css = ' . substr(sprintf('%o', fileperms('themes/default/style.css')), -4) . "\n";
  	$htmlStr .= 'themes/default/m_style.css = ' . substr(sprintf('%o', fileperms('themes/default/m_style.css')), -4) . "\n";
		return $htmlStr;
  }
  
  function getConnectivityDiag() {
  	$htmlStr = '';
  	$myhdhrurl = DVRUI_Vars::DVRUI_apiurl . 'discover';
  	$htmlStr .= "---------- CONNECTIVITY CHECKS --------------------------------------------------<br/>";
  	$htmlStr .= 'DVRUI_apiurl = ' . DVRUI_Vars::DVRUI_apiurl . "<br/>";
  	$htmlStr .= 'Discover URL = ' . $myhdhrurl . "<br/>";
  	$file      = fsockopen (parse_url($myhdhrurl, PHP_URL_HOST), 80, $errno, $errstr, 10);
  	if (! $file) {
  		$htmlStr .= 'Unable to open connection to ' . parse_url($myhdhrurl, PHP_URL_HOST) ;
  		$htmlStr .= '  Failed with error ' . $errno;
  		$htmlStr .= ' -  ' . $errstr . '<br/>';
  	}
  	else {
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
  	return $htmlStr;
  }

  function getHDHRDiag($hdhr) {
  	$devices =  $hdhr->device_count();
  	$hdhr_data = '';
  	$htmlStr =  "---------- HDHR TUNERS-----------------------------------------------------------<br/>";
  	for ($i=0; $i < $devices; $i++) {
  		$htmlStr .=  'tuner(' . $i . ') id: ' . $hdhr->get_device_id($i) . "<br/>";
  		$htmlStr .=  'tuner(' . $i . ') model: ' . $hdhr->get_device_modelName($i) . "<br/>";
  		$htmlStr .=  'tuner(' . $i . ') firmware: ' . $hdhr->get_device_fwVer($i) . "<br/>";
  		$htmlStr .=  'tuner(' . $i . ') baseurl: ' . $hdhr->get_device_baseurl($i) . "<br/>";
  		$htmlStr .=  'tuner(' . $i . ') auth: ' . $hdhr->get_device_auth($i) . "<br/>";
  	}
  	
  	$htmlStr .=  "---------- HDHR DVR ENGINES------------------------------------------------------<br/>";
  	$engines = $hdhr->engine_count();
  	$engines = 0;
  	for ($i=0; $i < $engines; $i++) {
  		$htmlStr .=  'dvr(' . $i . ') name: ' . $hdhr->get_engine_name($i) . "<br/>";
  		$htmlStr .=  'dvr(' . $i . ') version: ' . $hdhr->get_engine_version($i) . "<br/>";
  		$htmlStr .=  'dvr(' . $i . ') discover URL: ' . $hdhr->get_engine_discoverURL($i) . "<br/>";
  		$htmlStr .=  'dvr(' . $i . ') storage URL: ' . $hdhr->get_engine_storage_url($i) . "<br/>";
  		$htmlStr .=  'dvr(' . $i . ') storage ID: ' . $hdhr->get_engine_storage_id($i) . "<br/>";
  		$htmlStr .=  'dvr(' . $i . ') local IP: ' . $hdhr->get_engine_local_ip($i) . "<br/>";
  	}
		return $htmlStr;
  }

  function getAccountDiag($hdhr) {
  	$htmlStr =  "---------- HDHR Account -----------------------------------------------------------<br/>";
  	$devices =  $hdhr->device_count();
  	$url = DVRUI_Vars::DVRUI_apiurl . 'api/account?DeviceAuth=';
  	$auth = '';
  	for ($i=0; $i < $devices; $i++) {
  	  $auth .= $hdhr->get_device_auth($i);
    }

		if (in_array('curl', get_loaded_extensions())){
			$htmlStr .= 'curl extension = installed <br/>';
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url . $auth);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_TIMEOUT, 2);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
			$json = curl_exec($ch);
			curl_close($ch);
			$htmlStr .= 'curl returned = ' . $json . '<br/>';
		} else { 
			$htmlStr .= 'curl extension = not available <br/>';
		  $context = stream_context_create(
				array('http' => array(
					'header'=>'Connection: close\r\n',
					'timeout' => 2.0)));
	  	$json = file_get_contents($url . $auth,false,$context);	
  		$htmlStr .= 'stream returned = ' . $json . '<br/>';
		}
		return $htmlStr;
  }


clearCache();
echo '<pre>' . getServerDiag() . '</pre>';
echo '<pre>' . getConnectivityDiag() . '</pre>';
$hdhr = new DVRUI_HDHRjson();
echo '<pre>' . getHDHRDiag($hdhr) . '</pre>';
echo '<pre>' . getAccountDiag($hdhr) . '</pre>';
 



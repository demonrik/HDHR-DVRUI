<?php
require_once("includes/dvrui_hdhrjson.php");
require_once("includes/dvrui_common.php");
require_once("vars.php");
clearCache();
echo '<pre>';
echo "---------- OS and ENV VARIABLES ------------------------------------------------\n";
echo 'Operating System = ' . php_uname() . "\n";
echo 'HTTP_HOST = ' . getenv('HTTP_HOST') . "\n";
echo 'SERVER_NAME = ' . getenv('SERVER_NAME') . "\n";
echo 'SERVER_ADDR = ' . getenv('SERVER_ADDR') . "\n";
echo 'SERVER_SOFTWARE = ' . getenv('SERVER_SOFTWARE') . "\n";
echo 'DATE_LOCAL = ' . getenv('DATE_LOCAL') . "\n";
echo "---------- PHP INFO -------------------------------------------------------------\n";
echo 'php version = ' . phpversion() . "\n";
echo 'php.ini file = ' . php_ini_loaded_file() . "\n";
echo 'php.ini date.timezone = ' . ini_get('date.timezone') . "\n";
echo 'date_default_timezone_get() = ' . date_default_timezone_get() . "\n";
echo "---------- DVRUI VARS -----------------------------------------------------------\n";
echo 'DVRUI_name = ' . DVRUI_Vars::DVRUI_name . "\n";
echo 'DVRUI_version = ' . DVRUI_Vars::DVRUI_version . "\n";
echo 'DVRUI_TZ = ' . DVRUI_Vars::DVRUI_TZ . "\n";
echo "---------- PERMISSIONS -----------------------------------------------------------\n";
echo 'style = ' . substr(sprintf('%o', fileperms('style')), -4) . "\n";
echo 'themes = ' . substr(sprintf('%o', fileperms('themes')), -4) . "\n";
echo 'themes/default = ' . substr(sprintf('%o', fileperms('themes/default')), -4) . "\n";
echo 'themes/default/style.css = ' . substr(sprintf('%o', fileperms('themes/default/style.css')), -4) . "\n";
echo 'themes/default/m_style.css = ' . substr(sprintf('%o', fileperms('themes/default/m_style.css')), -4) . "\n";

$hdhr = new DVRUI_HDHRjson();
$devices =  $hdhr->device_count();
$hdhr_data = '';
echo "---------- HDHR TUNERS-----------------------------------------------------------\n";
for ($i=0; $i < $devices; $i++) {
	echo 'tuner(' . $i . ') id: ' . $hdhr->get_device_id($i) . "\n";
	echo 'tuner(' . $i . ') model: ' . $hdhr->get_device_model($i) . "\n";
	echo 'tuner(' . $i . ') firmware: ' . $hdhr->get_device_firmware($i) . "\n";
	echo 'tuner(' . $i . ') baseurl: ' . $hdhr->get_device_baseurl($i) . "\n";
}

echo "---------- HDHR DVR ENGINES------------------------------------------------------\n";
$engines = $hdhr->engine_count();
for ($i=0; $i < $engines; $i++) {
	echo 'dvr(' . $i . ') name: ' . $hdhr->get_engine_name($i) . "\n";
	echo 'dvr(' . $i . ') version: ' . $hdhr->get_engine_version($i) . "\n";
	echo 'dvr(' . $i . ') discover URL: ' . $hdhr->get_engine_discoverURL($i) . "\n";
	echo 'dvr(' . $i . ') storage URL: ' . $hdhr->get_engine_storage_url($i) . "\n";
	echo 'dvr(' . $i . ') storage ID: ' . $hdhr->get_engine_storage_id($i) . "\n";
	echo 'dvr(' . $i . ') local IP: ' . $hdhr->get_engine_local_ip($i) . "\n";

}

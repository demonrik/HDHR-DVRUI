<?php
	require_once("includes/dvrui_hdhrjson.php");
	require_once("includes/dvrui_common.php");
	require_once("includes/dvrui_tz.php");

class DVRUI_Config {
	private $config = array(
		0 => array (
			'name' => 'theme',
			'desc' => 'Name of the theme to use',
			'val' => 'default'
		),
		1 => array (
			'name' => 'rec_sort',
			'desc' => 'Sort Recordings by DD: Newest first, DA: Oldest First, T: Title name A-Z',
			'val' => 'DD'
		),
		2 => array (
			'name' => 'rule_view',
			'desc' => 'Display Rules Page as Tiles or List',
			'val' => 'Tile'
		),
		3 => array (
			'name' => 'series_view',
			'desc' => 'Display Series Page as Tiles or List',
			'val' => 'Tile'
		),
		4 => array (
			'name' => 'upcoming_view',
			'desc' => 'Display Upcoming Page as Tiles or List',
			'val' => 'Tile'
		),
		5 => array (
			'name' => 'recordings_view',
			'desc' => 'Display Recordings Page as Tiles or List',
			'val' => 'Tile'
		),
		6 => array (
			'name' => 'dashboard_recordings',
			'desc' => 'Number of latest Recordings to display on the dashboard',
			'val' => '10'
		),
		7 => array (
			'name' => 'dashboard_upcomings',
			'desc' => 'Number of upcoming Shows to display on the dashboard',
			'val' => '10'
		),
		8 => array (
			'name' => 'upcoming_days',
			'desc' => 'How many days to process in the Upcoming Page',
			'val' => '10'
		)
	);

  private $configFile = "config/config";

	private function loadConfig(){
		if (file_exists($this->configFile)) {
			$json = file_get_contents($this->configFile);
			$config = json_decode($json);
		}
	}
	
	public function storeConfig(){
		if (!empty($this->config)) {
			$json = json_encode($this->config);
			file_put_contents($this->configFile,$json);
		}
	}
	
	public function getConfig(){
		if (empty($this->config)) {
			$this->loadConfig();
		} else {
			return $this->config;
		}
	}
	
}
?>

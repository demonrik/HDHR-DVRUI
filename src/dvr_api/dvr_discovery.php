<?php
require_once("config/config.php");
require_once("common/common.php");

class dvr_discovery {

	private $key_devID = 'DeviceID';
	private $key_storageID = 'StorageID';
	private $key_localIP = 'LocalIP';
	private $key_baseURL = 'BaseURL';
	private $key_discoverURL = 'DiscoverURL';
	private $key_lineupURL = 'LineupURL';
	private $key_storageURL = 'StorageURL';
	
	private $dev_list = array();
	private $dvr_discovery_url ='';

	public function __construct() {
		$this->dvr_discovery_url = DVRUI_Config::DVRUI_apiurl . 'discover';
		error_log('Fetching discovery information from ' . $this->dvr_discovery_url);
		$json = getCachedJsonFromUrl($this->dvr_discovery_url);
		error_log('Got ['. print_r($json,true) . ']');
		
		for ($i=0;$i<count($json);$i++) {
			$device = $json[$i];
			error_log('Processing device ['. print_r($device,true) . ']');
			$dev_base = $device[$this->key_baseURL];
			$dev_ip = $device[$this->key_localIP];

			if (!array_key_exists($this->key_devID,$device)) {
				$dev_id = "";
			} else {
				$dev_id = $device[$this->key_devID];
			}
			
			if (!array_key_exists($this->key_discoverURL,$device)) {
				error_log('Legacy Device Found');
				$dev_discovery = '';
				$dev_lineup = '';
				$dev_storage= '';
			} else {
				$dev_discovery = $device[$this->key_discoverURL];
				if (!array_key_exists($this->key_storageID,$device)) {
					$dev_strid = "";
				} else {
					$dev_strid = $device[$this->key_storageID];
				}
				
				if (!array_key_exists($this->key_storageURL,$device)) {
					$dev_storage = '';
				} else {
					$dev_storage = $device[$this->key_storageURL];
				}
			
				if (!array_key_exists($this->key_lineupURL,$device)) {
					$dev_lineup = '';
				} else {
					$dev_lineup = $device[$this->key_lineupURL];
				}
			}
			
			error_log('Adding to Device List');
			$this->dev_list[] = array(
				$this->key_devID=>$dev_id,
				$this->key_storageID=>$dev_strid,
				$this->key_localIP=>$dev_ip,
				$this->key_baseURL=>$dev_base,
				$this->key_discoverURL=>$dev_discovery,
				$this->key_lineupURL=>$dev_lineup,
				$this->key_storageURL=>$dev_storage);
			
			continue;
		}
	}
	
	public function dvr_discovery() {
		self::__construct();
	}

	public function device_count() {
		return count($this->dev_list);
	}

	public function is_legacy_device($pos) {
		$dev = $this->dev_list[$pos];
		if ($dev[$this->key_discoverURL] != '') {
			return false;
		}
		return true;
	}

	public function is_recorder($pos) {
		$dev = $this->dev_list[$pos];
		return ($dev[$this->key_storageURL] != '');
	}

	public function is_tuner($pos) {
		$dev = $this->dev_list[$pos];
		return ($dev[$this->key_lineupURL] != '');
	}

	public function get_device_id($pos) {
		$dev = $this->dev_list[$pos];
		return $dev[$this->key_devID];
	}

	public function get_storage_id($pos) {
		$dev = $this->dev_list[$pos];
		return $dev[$this->key_storageID];
	}

	public function get_local_ip($pos) {
		$dev = $this->dev_list[$pos];
		return $dev[$this->key_localIP];
	}

	public function get_base_url($pos) {
		$dev = $this->dev_list[$pos];
		return $dev[$this->key_baseURL];
	}

	public function get_discover_url($pos) {
		$dev = $this->dev_list[$pos];
		return $dev[$this->key_discoverURL];
	}

	public function get_lineup_url($pos) {
		$dev = $this->dev_list[$pos];
		return $dev[$this->key_lineupURL];
	}

	public function get_storage_url($pos) {
		$dev = $this->dev_list[$pos];
		return $dev[$this->key_storageURL];
	}

}

?>
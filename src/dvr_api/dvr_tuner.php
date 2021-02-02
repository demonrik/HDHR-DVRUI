<?php
require_once("common/common.php");

class dvr_tuner {
	private $key_modelName = 'FriendlyName';
	private $key_modelNum = 'ModelNumber';
	private $key_fwName = 'FirmwareName';
	private $key_fwVer = 'FirmwareVersion';
	private $key_devID = 'DeviceID';
	private $key_auth = 'DeviceAuth';
	private $key_baseURL = 'BaseURL';
	private $key_lineupURL = 'LineupURL';
	private $key_tuners = 'TunerCount';

	private $discover_url = '';
	private $model_name = '';
	private $model_number = '';
	private $firmware_name = '';
	private $firmware_version = '';
	private $device_id = '';
	private $device_auth = '';
	private $base_url = '';
	private $lineup_url = '';
	private $tuners = '';
	private $channel_count = '';

	public function dvr_tuner($url) {
		error_log('Fetching discovery information from ' . $url);
		$json = getCachedJsonFromUrl($url);
		error_log('Got ['. print_r($json,true) . ']');
		$this->discover_url = $url;
		$this->process_discover($json);
	}
	
	private function process_discover($device) {
		if (array_key_exists($this->key_modelName,$device)){
			$this->model_name = $device[$this->key_modelName];
		}
		if (array_key_exists($this->key_modelNum,$device)){
			$this->model_number = $device[$this->key_modelNum];
		}
		if (array_key_exists($this->key_fwName,$device)){
			$this->firmware_name = $device[$this->key_fwName];
		}
		if (array_key_exists($this->key_fwVer,$device)){
			$this->firmware_version = $device[$this->key_fwVer];
		}
		if (array_key_exists($this->key_devID,$device)){
			$this->device_id = $device[$this->key_devID];
		}
		if (array_key_exists($this->key_auth,$device)){
			$this->device_auth = $device[$this->key_auth];
		}
		if (array_key_exists($this->key_baseURL,$device)){
			$this->base_url = $device[$this->key_baseURL];
		}
		if (array_key_exists($this->key_lineupURL,$device)){
			$this->lineup_url = $device[$this->key_lineupURL];
			$this->process_lineup($this->lineup_url);
		}
		if (array_key_exists($this->key_tuners,$device)){
			$this->tuners = $device[$this->key_tuners];
		}
	}
	
	private function process_lineup($url) {
		error_log('Fetching Lineup information from ' . $url);
		$json = getCachedJsonFromUrl($url);
		error_log('Got ['. print_r($json,true) . ']');
		$this->channel_count = count($json);
	}


	public function get_image_url() {
		switch ($this->model_number) {
			case 'HDTC-2US':
				return './images/HDTC-2US.png';
			case 'HDHR3-CC':
				return './images/HDHR3-CC.png';
			case 'HDHR3-4DC':
				return './images/HDHR3-4DC.png';
			case 'HDHR3-EU':
			case 'HDHR3-US':
			case 'HDHR3-DT':
				return './images/HDHR3-US.png';
			case 'HDHR4-2US':
			case 'HDHR4-2DT':
				return './images/HDHR4-2US.png';
			case 'HHDD-2TB':
			case 'HDVR-2US-1TB':
			case 'HDVR-4US-1TB':
				return './images/HDVR-US.png';
			case 'HDHR5-DT':
			case 'HDHR5-2US':
			case 'HDHR5-4DC':
			case 'HDHR5-4DT':
			case 'HDHR5-4US':
				return './images/HDHR5-US.png';
			case 'HDHR5-6CC':
				return './images/HDHR5-6CC.png';
			case 'TECH4-2DT':
			case 'TECH4-2US':
				return './images/TECH4-2US.png';
			case 'TECH4-8US':
				return './images/TECH4-8US.png';
			case 'TECH5-36CC':
				return './images/TECH5-36CC.png';
			case 'TECH5-16DC':
			case 'TECH5-16DT':
				return './images/TECH5-16DT.png';
			default:
				return './images/HDHR5-US.png';
		}
	}

	public function get_model_name() {
		return $this->model_name;
	}
	public function get_model_number() {
		return $this->model_number;
	}
	public function get_firmware_name() {
		return $this->firmware_name;
	}
	public function get_firmware_version() {
		return $this->firmware_version;
	}
	public function get_device_id() {
		return $this->device_id;
	}
	public function get_device_auth() {
		return $this->device_auth;
	}
	public function get_base_url() {
		return $this->base_url;
	}
	public function get_lineup_url() {
		return $this->lineup_url;
	}
	public function get_channel_count() {
		return $this->channel_count;
	}
	public function get_tuners() {
		return $this->tuners;
	}
}

?>
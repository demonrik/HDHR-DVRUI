<?php
require_once("common/common.php");
require_once("dvr_api/dvr_series.php");

class dvr_engine {
	private $key_model_name = 'FriendlyName';
	private $key_model_number = 'ModelNumber';
	private $key_base_url = 'Version';
	private $key_version = 'BaseURL';
	private $key_storage_id = 'StorageID';
	private $key_storage_url = 'StorageURL';
	private $key_total_space = 'TotalSpace';
	private $key_free_space = 'FreeSpace';

	private $discover_url = '';
	private $model_name = '';
	private $model_number = '';
	private $base_url = '';
	private $version = '';
	private $storage_id = '';
	private $storage_url = '';
	private $total_space = '';
	private $free_space = '';
	private $series_count = '';
	private $recordings_count = 0;


	public function dvr_engine($url) {
		error_log('Fetching discovery information from ' . $url);
		$json = getCachedJsonFromUrl($url);
		error_log('Got ['. print_r($json,true) . ']');
		$this->discover_url = $url;
		$this->process_discover($json);
	}

	private function process_discover($device) {
		if (array_key_exists($this->key_model_name,$device)){
			$this->model_name = $device[$this->key_model_name];
		}
		if (array_key_exists($this->key_model_number,$device)){
			$this->model_number = $device[$this->key_model_number];
		}
		if (array_key_exists($this->key_base_url,$device)){
			$this->base_url = $device[$this->key_base_url];
		}
		if (array_key_exists($this->key_version,$device)){
			$this->version = $device[$this->key_version];
		}
		if (array_key_exists($this->key_storage_id,$device)){
			$this->storage_id = $device[$this->key_storage_id];
		}
		if (array_key_exists($this->key_storage_url,$device)){
			$this->storage_url = $device[$this->key_storage_url];
			$this->count_series($this->storage_url);
		}
		if (array_key_exists($this->key_total_space,$device)){
			$this->total_space = $this->convert_size($device[$this->key_total_space]);
		} else {
			$this->total_space = 'N/A';
		}
		if (array_key_exists($this->key_free_space,$device)){
			$this->free_space = $this->convert_size($device[$this->key_free_space]);
		} else {
			$this->free_space = 'N/A';
		}
	}

	private function count_series($url) {
		error_log('Fetching Series information from ' . $url);
		$json = getCachedJsonFromUrl($url);
		error_log('Got ['. print_r($json,true) . ']');
		$this->series_count = count($json);
		for ($i = 0; $i < count ($json) ; $i++) {
			$series = new dvr_series($json[$i]);
			$this->recordings_count += $series->get_episodes_count();
		}
	}

	private function convert_size($bytes){
		$bytes = floatval($bytes);
		if ($bytes == 0) {
			return 'FULL';
		}

		$arBytes = array(
  			0 => array(
				"UNIT" => "TB",
				"VALUE" => pow(1024, 4)),
			1 => array(
				"UNIT" => "GB",
				"VALUE" => pow(1024, 3)),
			2 => array(
				"UNIT" => "MB",
				"VALUE" => pow(1024, 2)),
			3 => array(
				"UNIT" => "KB",
				"VALUE" => 1024),
			4 => array(
				"UNIT" => "B",
				"VALUE" => 1));
  	
		foreach($arBytes as $arItem) {
			if($bytes >= $arItem["VALUE"]){
				$result = $bytes / $arItem["VALUE"];
				$result = strval(round($result, 2))." ".$arItem["UNIT"];
				break;
			}
		}
		return $result;
	}

	public function get_image_url() {
		switch ($this->model_number) {
			case '':
				return './images/HDHR-DVR.png';
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
	public function get_base_url() {
		return $this->base_url;
	}
	public function get_version() {
		return $this->version;
	}
	public function get_storage_id() {
		return $this->storage_id;
	}
	public function get_storage_url() {
		return $this->storage_url;
	}
	public function get_total_space() {
		return $this->total_space;
	}
	public function get_free_space() {
		return $this->free_space;
	}
	public function get_recordings_count() {
		return $this->recordings_count;
	}
	public function get_series_count() {
		return $this->series_count;
	}

}
?>
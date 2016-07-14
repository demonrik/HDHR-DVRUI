<?php

class DVRUI_HDHRjson {
	private $myhdhrurl = 'http://ipv4.my.hdhomerun.com/discover';
	private $hdhrkey_devID = 'DeviceID';
	private $hdhrkey_localIP = 'LocalIP';
	private $hdhrkey_baseURL = 'BaseURL';
	private $hdhrkey_discoverURL = 'DiscoverURL';
	private $hdhrkey_lineupURL = 'LineupURL';
	private $hdhrkey_modelNum = 'ModelNumber';
	private $hdhrkey_modelName = 'FriendlyName';
	private $hdhrkey_auth = 'DeviceAuth';
	private $hdhrkey_fwVer = 'FirmwareVersion';
	private $hdhrkey_Ver = 'Version';
	private $hdhrkey_fwName = 'FirmwareName';
	private $hdhrkey_tuners = 'TunerCount';
	private $hdhrkey_free = 'FreeSpace';

	private $hdhrkey_storageID = 'StorageID';
	private $hdhrkey_storageURL = 'StorageURL';

	private $hdhrlist = array();
	private $enginelist = array();
	private $hdhrlist_key_channelcount = 'ChannelCount';
	private $storageURL = "??";
	
	public function DVRUI_HDHRjson() {
		if (in_array('curl', get_loaded_extensions())){
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $this->myhdhrurl);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_TIMEOUT, 2);
			$json = curl_exec($ch);
			curl_close($ch);
		} else { 
			$context = stream_context_create(
				array('http' => array(
					'header'=>'Connection: close\r\n',
					'timeout' => 2.0)));
			$json = file_get_contents($this->myhdhrurl,false,$context);	
		}

		$hdhr_data = json_decode($json, true);
		for ($i=0;$i<count($hdhr_data);$i++) {
			$hdhr = $hdhr_data[$i];
			$hdhr_base = $hdhr[$this->hdhrkey_baseURL];
			$hdhr_ip = $hdhr[$this->hdhrkey_localIP];
			
			if (!array_key_exists($this->hdhrkey_discoverURL,$hdhr)) {
				// Skip this HDHR - it doesn't support the newer HTTP interface
				// for DVR
				continue;
			}

                        if (in_array('curl', get_loaded_extensions())){
                                $ch = curl_init();
                                curl_setopt($ch, CURLOPT_URL, $hdhr[$this->hdhrkey_discoverURL]);
                                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                                curl_setopt($ch, CURLOPT_TIMEOUT, 2);
                                $hdhr_info_json = curl_exec($ch);
                                curl_close($ch);
                        } else {
                                $context = stream_context_create(
                                        array('http' => array(
                                                'header'=>'Connection: close\r\n',
                                                'timeout' => 2.0)));
                                $hdhr_info_json = file_get_contents($hdhr[$this->hdhrkey_discoverURL],false,$context);
                        }




			$hdhr_info = json_decode($hdhr_info_json, true);


			if (array_key_exists($this->hdhrkey_storageURL,$hdhr)) {
				// this is a record engine!
				$this->storageURL = $hdhr[$this->hdhrkey_storageURL];
				$this->enginelist[] = array ($this->hdhrkey_storageID => $hdhr[$this->hdhrkey_storageID],
									$this->hdhrkey_modelName => $hdhr_info[$this->hdhrkey_localIP],
									$this->hdhrkey_Ver => $hdhr_info[$this->hdhrkey_Ver],
									$this->hdhrkey_free => $hdhr_info[$this->hdhrkey_free],
									$this->hdhrkey_localIP => $hdhr[$this->hdhrkey_localIP],
									$this->hdhrkey_baseURL => $hdhr[$this->hdhrkey_baseURL],
									$this->hdhrkey_discoverURL => $hdhr[$this->hdhrkey_discoverURL],
									$this->hdhrkey_storageURL => $hdhr[$this->hdhrkey_storageURL]);

				continue;
			}
			if (in_array('curl', get_loaded_extensions())){
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, $hdhr[$this->hdhrkey_lineupURL]);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($ch, CURLOPT_TIMEOUT, 2);
				$hdhr_lineup_json = curl_exec($ch);
				curl_close($ch);
			} else { 
				$context = stream_context_create(
					array('http' => array(
						'header'=>'Connection: close\r\n',
						'timeout' => 2.0)));
				$hdhr_lineup_json = file_get_contents($hdhr[$this->hdhrkey_lineupURL],false,$context);	
			}
		


			$hdhr_lineup = json_decode($hdhr_lineup_json, true);
		
			if (array_key_exists($this->hdhrkey_tuners,$hdhr_info)) {
				$this->hdhrlist[] = array( $this->hdhrkey_devID => $hdhr[$this->hdhrkey_devID],
											$this->hdhrkey_modelNum => $hdhr_info[$this->hdhrkey_modelNum],
											$this->hdhrlist_key_channelcount => count($hdhr_lineup),
											$this->hdhrkey_baseURL => $hdhr_base,
											$this->hdhrkey_lineupURL => $hdhr[$this->hdhrkey_lineupURL],
											$this->hdhrkey_modelName =>$hdhr_info[$this->hdhrkey_modelName],
											$this->hdhrkey_auth =>$hdhr_info[$this->hdhrkey_auth],
											$this->hdhrkey_fwVer => $hdhr_info[$this->hdhrkey_fwVer],
											$this->hdhrkey_tuners => $hdhr_info[$this->hdhrkey_tuners],
											$this->hdhrkey_fwName => $hdhr_info[$this->hdhrkey_fwName]);
			} else {
				$this->hdhrlist[] = array( $this->hdhrkey_devID => $hdhr[$this->hdhrkey_devID],
											$this->hdhrkey_modelNum => $hdhr_info[$this->hdhrkey_modelNum],
											$this->hdhrlist_key_channelcount => count($hdhr_lineup),
											$this->hdhrkey_baseURL => $hdhr_base,
											$this->hdhrkey_lineupURL => $hdhr[$this->hdhrkey_lineupURL],
											$this->hdhrkey_modelName =>$hdhr_info[$this->hdhrkey_modelName],
											$this->hdhrkey_auth =>$hdhr_info[$this->hdhrkey_auth],
											$this->hdhrkey_fwVer => $hdhr_info[$this->hdhrkey_fwVer],
											$this->hdhrkey_fwName => $hdhr_info[$this->hdhrkey_fwName]);
			}
		}
	}
	
	public function device_count() {
		return count($this->hdhrlist);
	}
	public function engine_count() {
		return count($this->enginelist);
	}

	public function get_device_info($pos) {
		$device = $this->hdhrlist[$pos];
		return ' DeviceID: ' . $device[$this->hdhrkey_devID] 
		          . ' Model Number: ' . $device[$this->hdhrkey_modelNum] 
		          . ' Channels: ' . $device[$this->hdhrlist_key_channelcount] . ' ';
	}
	public function get_storage_url(){
		return $this->storageURL;
	}	
	public function get_engine_storage_url($pos){
		$engine = $this->enginelist[$pos];
		return $engine[$this->hdhrkey_storageURL];
	}
	public function get_engine_storage_id($pos){
		$engine = $this->enginelist[$pos];
		return $engine[$this->hdhrkey_storageID];
	}
	public function get_engine_local_ip($pos){
		$engine = $this->enginelist[$pos];
		return $engine[$this->hdhrkey_localIP];
	}
	public function get_engine_image($pos) {
		return "https://www.silicondust.com/wp-content/uploads/2016/03/dvr-logo.png";
	}                                             


	public function get_device_id($pos) {
		$device = $this->hdhrlist[$pos];
		return $device[$this->hdhrkey_devID];
	}

	public function get_device_model($pos) {
		$device = $this->hdhrlist[$pos];
		return $device[$this->hdhrkey_modelNum];
	}

	public function get_device_channels($pos) {
		$device = $this->hdhrlist[$pos];
		return $device[$this->hdhrlist_key_channelcount];
	}

	public function get_device_lineup($pos) {
		$device = $this->hdhrlist[$pos];
		return $device[$this->hdhrkey_lineupURL];
	}

	public function get_device_baseurl($pos) {
		$device = $this->hdhrlist[$pos];
		return $device[$this->hdhrkey_baseURL];
	}

	public function get_device_firmware($pos) {
		$device = $this->hdhrlist[$pos];
		return $device[$this->hdhrkey_fwVer];
	}

	public function get_device_tuners($pos) {
		$device = $this->hdhrlist[$pos];
		if (array_key_exists($this->hdhrkey_tuners,$device)) {
			return $device[$this->hdhrkey_tuners];
		} else {
			return '??';
		}
	}

	public function get_device_auth($pos) {
		$device = $this->hdhrlist[$pos];
		if (array_key_exists($this->hdhrkey_auth,$device)) {
			return $device[$this->hdhrkey_auth];
		} else {
			return '??';
		}
	}
	
	public function get_device_image($pos) {
		$device = $this->hdhrlist[$pos];
		switch ($device[$this->hdhrkey_modelNum]) {
			case 'HDTC-2US':
				return 'https://www.silicondust.com/wordpress/wp-content/uploads/2016/04/extend-logo-2.png';
			case 'HDHR3-CC':
			case 'HDHR3-6CC-3X2':
				return 'https://www.silicondust.com/wordpress/wp-content/uploads/2016/04/prime-logo-2.png';
			case 'HDHR3-EU':
			case 'HDHR3-4DC':
				return 'https://www.silicondust.com/wordpress/wp-content/uploads/2016/04/expand-logo-2.png';
			case 'HDHR3-US':
			case 'HDHR3-DT':
				return './images/notsupported.png';
			case 'HDHR4-2US':
			case 'HDHR4-2DT':
				return 'https://www.silicondust.com/wordpress/wp-content/uploads/2016/04/connect-logo.png';
			default:
				return $device[$this->hdhrkey_modelNum];
		}
	}
}
?>

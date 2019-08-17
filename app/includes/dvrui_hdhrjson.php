<?php
	require_once("includes/dvrui_common.php");
	require_once("vars.php");

class DVRUI_HDHRjson {
	
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
	private $hdhrkey_freespace = 'FreeSpace';
	private $hdhrkey_legacy = 'Legacy';
	private $hdhrlist_key_channelcount = 'ChannelCount';

	private $myhdhrurl = "";
	private $auth = '';

	private $hdhrlist = array();
	private $enginelist = array();
	
	public function DVRUI_HDHRjson() {

		$this->myhdhrurl = DVRUI_Vars::DVRUI_apiurl . 'discover';
		
		if(DVRUI_Vars::DVRUI_discover_cache != ''){
			$cachesecs = DVRUI_Vars::DVRUI_discover_cache;
		}else{
			$cachesecs = 60;
		}
		
		$hdhr_data = getCachedJsonFromUrl($this->myhdhrurl,$cachesecs);
		for ($i=0;$i<count($hdhr_data);$i++) {
			$hdhr = $hdhr_data[$i];
			$hdhr_base = $hdhr[$this->hdhrkey_baseURL];
			$hdhr_ip = $hdhr[$this->hdhrkey_localIP];
			
			if (!array_key_exists($this->hdhrkey_discoverURL,$hdhr)) {
				// Skip this HDHR - it doesn't support the newer HTTP interface
				// for DVR
				continue;
			}

			$hdhr_info = getCachedJsonFromUrl($hdhr[$this->hdhrkey_discoverURL],$cachesecs);

			if (array_key_exists($this->hdhrkey_storageURL,$hdhr)) {
				// this is a record engine!

				// Need to confirm it's a valid one - After restart of
				// engine it updates my.hdhomerun.com but sometimes the
				// old engine config is left behind.
				$rEngine = getJsonFromUrl($hdhr[$this->hdhrkey_discoverURL]);
				if (strcasecmp($rEngine[$this->hdhrkey_storageID],$hdhr[$this->hdhrkey_storageID]) != 0) {
					//skip, this is not our engine
					error_log('Engine found - but storageID not matching discover - skipping');
					continue;
    		}
				
				error_log('Adding engine ' . $hdhr_base); 
				// freespace is not available if maxstreams = 0
				$freespace = '0';
		  	if (array_key_exists($this->hdhrkey_freespace,$hdhr_info)) {
	  			$freespace = $hdhr_info[$this->hdhrkey_freespace];
  			}

				// for new Servio and Scribe there are additional params needed to store
		  	if (array_key_exists($this->hdhrkey_modelNum,$hdhr_info)) {
	  			$enginemodel = $hdhr_info[$this->hdhrkey_modelNum];
	  		} else {
	  			$enginemodel = 'RecordEngine';
  			}
		  	if (array_key_exists($this->hdhrkey_devID,$hdhr_info)) {
	  			$engineID = $hdhr_info[$this->hdhrkey_devID];
	  		} else {
	  			$engineID = 'Record Engine';
  			}
		  	if (array_key_exists($this->hdhrkey_tuners,$hdhr_info)) {
	  			$engineTuners = $hdhr_info[$this->hdhrkey_tuners];
	  		} else {
	  			$engineTuners = '??';
  			}
		  	if (array_key_exists($this->hdhrkey_auth,$hdhr_info)) {
	  			$engineAuth = $hdhr_info[$this->hdhrkey_auth];
					$this->auth .= $hdhr_info[$this->hdhrkey_auth];
	  		} else {
	  			$engineAuth = '??';
  			}
		  	if (preg_match('/\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}/', $hdhr_base, $ip_match)) {
	  			$localIP = $ip_match[0];
	  		} else {
	  			$localIP = $hdhr_base;
  			}
  			
				$this->enginelist[] = array( $this->hdhrkey_storageID => $hdhr[$this->hdhrkey_storageID],
					$this->hdhrkey_baseURL => $hdhr_base,
					$this->hdhrkey_localIP => $localIP,
					$this->hdhrkey_devID => $engineID,
					$this->hdhrkey_tuners => $engineTuners,
					$this->hdhrkey_auth => $engineAuth,
					$this->hdhrkey_modelNum => $enginemodel,
					$this->hdhrkey_modelName => $hdhr_info[$this->hdhrkey_modelName],
					$this->hdhrkey_Ver => $hdhr_info[$this->hdhrkey_Ver],
					$this->hdhrkey_storageID => $hdhr_info[$this->hdhrkey_storageID],
					$this->hdhrkey_storageURL => $hdhr_info[$this->hdhrkey_storageURL],
					$this->hdhrkey_freespace => $freespace);
				continue;
			}
			// ELSE we have a tuner
		
			$tuners='??';
			if (array_key_exists($this->hdhrkey_tuners,$hdhr_info)) {
				$tuners = $hdhr_info[$this->hdhrkey_tuners];
			}

			$legacy='No';
			if (array_key_exists($this->hdhrkey_legacy,$hdhr_info)) {
				$legacy = $hdhr_info[$this->hdhrkey_legacy];
			}

			$hdhr_lineup = getCachedJsonFromUrl($hdhr_info[$this->hdhrkey_lineupURL],$cachesecs);	

			$this->hdhrlist[] = array( $this->hdhrkey_devID => $hdhr[$this->hdhrkey_devID],
										$this->hdhrkey_modelNum => $hdhr_info[$this->hdhrkey_modelNum],
										$this->hdhrlist_key_channelcount => count($hdhr_lineup),
										$this->hdhrkey_baseURL => $hdhr_base,
										$this->hdhrkey_discoverURL => $hdhr[$this->hdhrkey_discoverURL],
										$this->hdhrkey_lineupURL => $hdhr_info[$this->hdhrkey_lineupURL],
										$this->hdhrkey_modelName => $hdhr_info[$this->hdhrkey_modelName],
										$this->hdhrkey_localIP => $hdhr[$this->hdhrkey_localIP],
										$this->hdhrkey_auth =>$hdhr_info[$this->hdhrkey_auth],
										$this->hdhrkey_fwVer => $hdhr_info[$this->hdhrkey_fwVer],
										$this->hdhrkey_tuners => $tuners,
										$this->hdhrkey_legacy => $legacy,
										$this->hdhrkey_fwName => $hdhr_info[$this->hdhrkey_fwName]);
			$this->auth .= $hdhr_info[$this->hdhrkey_auth];
		}
	}

	public function get_auth() {
		return $this->auth;
	}
	
	public function device_count() {
		return count($this->hdhrlist);
	}

	public function engine_count() {
		return count($this->enginelist);
	}

	public function get_engine_id($pos) {
		$engine = $this->enginelist[$pos];
		return $engine[$this->hdhrkey_devID];
	}

	public function get_engine_base_url($pos){
		$engine = $this->enginelist[$pos];
		return $engine[$this->hdhrkey_baseURL];
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
		$engine = $this->enginelist[$pos];
		return $this->get_image_url($engine[$this->hdhrkey_modelNum]);
	}

	public function get_engine_name($pos) {
		return $this->enginelist[$pos][$this->hdhrkey_modelName];
	}
	
	public function get_engine_version($pos) {
		return $this->enginelist[$pos][$this->hdhrkey_Ver];
	}
	
	public function get_engine_modelNum($pos) {
		$engine = $this->enginelist[$pos];
		return $engine[$this->hdhrkey_modelNum];
	}

	public function get_engine_modelName($pos) {
		$engine = $this->enginelist[$pos];
		return $engine[$this->hdhrkey_modelName];
	}

	public function get_engine_tuners($pos) {
		$engine = $this->enginelist[$pos];
		return $engine[$this->hdhrkey_tuners];
	}

	public function get_engine_space($pos) {
		$engine = $this->enginelist[$pos];
		return $this->convert_size($engine[$this->hdhrkey_freespace]);
	}

	public function get_engine_auth($pos) {
		$engine = $this->enginelist[$pos];
		return $engine[$this->hdhrkey_auth];
	}
	
	public function get_engine_discoverURL($pos) {
		return $this->enginelist[$pos][$this->hdhrkey_discoverURL];
	}

	public function poke_engine($pos) {
		$url = $this->enginelist[$pos][$this->hdhrkey_baseURL] . "/recording_events.post?sync";
		// Newer engine requiires POST instead of GET
		postToUrl($url,'');
	}

	public function get_device_id($pos) {
		$device = $this->hdhrlist[$pos];
		return $device[$this->hdhrkey_devID];
	}

	public function get_device_local_ip($pos){
		$engine = $this->hdhrlist[$pos];
		return $engine[$this->hdhrkey_localIP];
	}

	public function get_device_modelName($pos) {
		$device = $this->hdhrlist[$pos];
		return $device[$this->hdhrkey_modelName];
	}

	public function get_device_modelNum($pos) {
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

	public function get_device_fwVer($pos) {
		$device = $this->hdhrlist[$pos];
		return $device[$this->hdhrkey_fwVer];
	}

	public function get_device_fwName($pos) {
		$device = $this->hdhrlist[$pos];
		return $device[$this->hdhrkey_fwName];
	}

	public function get_device_tuners($pos) {
		$device = $this->hdhrlist[$pos];
		return $device[$this->hdhrkey_tuners];
	}

	public function get_device_legacy($pos) {
		$device = $this->hdhrlist[$pos];
		if( $device[$this->hdhrkey_legacy] == 1) {
			return 'Legacy';
		}
		else {
			return 'HTTP';
		}
	}

	public function get_device_auth($pos) {
		$device = $this->hdhrlist[$pos];
		return $device[$this->hdhrkey_auth];
	}
	
	public function get_device_image($pos) {
		$device = $this->hdhrlist[$pos];
		return $this->get_image_url($device[$this->hdhrkey_modelNum]);
	}

	public function get_image_url($model) {
		switch ($model) {
			case 'RecordEngine':
				return './images/HDHR-DVR.png';
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

  private function convert_size($bytes)
  {
  	$bytes = floatval($bytes);
  	$arBytes = array(
  		0 => array(
  			"UNIT" => "TB",
  			"VALUE" => pow(1024, 4)
  		),
  		1 => array(
  			"UNIT" => "GB",
  			"VALUE" => pow(1024, 3)
  		),
  		2 => array(
  			"UNIT" => "MB",
  			"VALUE" => pow(1024, 2)
  			),
  		3 => array(
  			"UNIT" => "KB",
  			"VALUE" => 1024
  		),
  		4 => array(
  			"UNIT" => "B",
  			"VALUE" => 1
  		),
  	);
  	
  	if ($bytes == 0) {
  	  return 'FULL';
  	}
  	
  	foreach($arBytes as $arItem)
  	{
  		if($bytes >= $arItem["VALUE"])
  		{
  			$result = $bytes / $arItem["VALUE"];
  			$result = strval(round($result, 2))." ".$arItem["UNIT"];
  			break;
  		}
  	}
  	return $result;
  }

}
?>

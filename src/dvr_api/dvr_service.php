<?php
require_once("config/config.php");
require_once("common/common.php");

/*
 * Simple class to manage access to SiliconDusts servers
 */
class dvr_service {
	
	private $baseURL = 'http://api.hdhomerun.com/';
	private $rulesExt =  'api/recording_rules?DeviceAuth=';
	
	private $auth='';
	
	public function __construct() {
	}

	public function dvr_service() {
		self::__construct();
	}
	
	public function set_auth($auth) {
		$this->auth = $auth;
	}
	
	public function getRules() {
		error_log("Fetching Rules from " . $this->baseURL .$this->rulesExt . $this->auth);
		return getJsonFromUrl($this->baseURL .$this->rulesExt . $this->auth);
	}
}
	
?>
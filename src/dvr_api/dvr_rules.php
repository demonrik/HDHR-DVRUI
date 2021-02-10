<?php
require_once("config/config.php");
require_once("common/common.php");

class dvr_rules {
	/*
	 * Documentation on How the HDHR system exposes Recording rules is explained
	 * https://github.com/Silicondust/documentation/wiki/DVR%20Recording%20Rules
	 */
	private $recording_RecID = 'RecordingRuleID';
	private $recording_SeriesID = 'SeriesID';
	private $recording_Title = 'Title';
	private $recording_ImageURL = 'ImageURL';
	private $recording_Priority = 'Priority';
	private $recording_StartPad = 'StartPadding';
	private $recording_EndPad = 'EndPadding';
	private $recording_Synopsis = 'Synopsis';
	private $recording_Recent = 'RecentOnly';
	private $recording_Channel = 'ChannelOnly';
	private $recording_Team = 'TeamOnly';
	private $recording_Airdate = 'AfterOriginalAirdateOnly';
	private $recording_DateTimeOnly = 'DateTimeOnly';

	
	private $recordingCmd_delete = '&Cmd=delete';
	private $recordingCmd_change = '&Cmd=change';
	private $recordingCmd_add = '&Cmd=add';
	
	private $rules = array();
	
	public function __construct($auth) {
		$rulesURL =  DVRUI_Config::DVRUI_apiurl . 'api/recording_rules?DeviceAuth=' . $auth;
		error_log("Processing Rules " . $rulesURL);
		$rules_info = getJsonFromUrl($rulesURL);
		for ($i = 0; $i < count($rules_info); $i++) {
			$this->processRule($rules_info[$i]);
		}
	}

	public function dvr_rules($auth) {
		self::__construct($auth);
	}

	private function processRule($rule) {
		$recID = $rule[$this->recording_RecID];
		$seriesID = $rule[$this->recording_SeriesID];
		$title = $rule[$this->recording_Title];
		$startPad = 0;
		$endPad = 0;
		$priority = 'X';
		$dateTimeOnly = '';
		$channelOnly = 'All Channels';
		$teamOnly = 'X';
		$recentOnly = 'X';
		$airdate = '';
		$synopsis = "";
		$image = NO_IMAGE;

		error_log("Processing Rule.... " . print_r($rule, true));
			
		if (array_key_exists($this->recording_ImageURL,$rule)){
			$image = $rule[$this->recording_ImageURL];
		}
		if (array_key_exists($this->recording_Synopsis,$rule)){
			$synopsis = $rule[$this->recording_Synopsis];
		}
		if (array_key_exists($this->recording_Priority,$rule)){
			$priority = $rule[$this->recording_Priority];
		}
		if (array_key_exists($this->recording_DateTimeOnly,$rule)){
			$dateTimeOnly = $rule[$this->recording_DateTimeOnly];
		}
		if (array_key_exists($this->recording_Channel,$rule)) {
			$channelOnly = 'Channel' . $rule[$this->recording_Channel];
		}
		if (array_key_exists($this->recording_Team,$rule)) {
			$teamOnly = $rule[$this->recording_Team];
		}
		if (array_key_exists($this->recording_Recent,$rule)) {
			$recentOnly = $rule[$this->recording_Recent];
		}
		if (array_key_exists($this->recording_Airdate,$rule)) {
			$airdate = $rule[$this->recording_Airdate];
		}
		if (array_key_exists($this->recording_StartPad,$rule)) {
			$startPad = $rule[$this->recording_StartPad];
		}
		if (array_key_exists($this->recording_EndPad,$rule)) {
			$endPad = $rule[$this->recording_EndPad];
		}
			
		$this->rules[] = array($this->recording_RecID => $recID,
				$this->recording_SeriesID => $seriesID,
				$this->recording_ImageURL => $image,
				$this->recording_Title => $title,
				$this->recording_Synopsis => $synopsis,
				$this->recording_StartPad => $startPad,
				$this->recording_EndPad => $endPad,
				$this->recording_Priority => $priority,
				$this->recording_DateTimeOnly => $dateTimeOnly,
				$this->recording_Channel => $channelOnly,
				$this->recording_Team => $teamOnly,
				$this->recording_Recent => $recentOnly,
				$this->recording_Airdate => $airdate);
	}
	
}

?>
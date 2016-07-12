<?php
	require_once("includes/dvrui_hdhrjson.php");

class DVRUI_Rules {
	/*
	 * Documentation on How the HDHR system exposes Recording rules is explained
	 * https://github.com/Silicondust/documentation/wiki/DVR%20Recording%20Rules
	 */
	private $recordingsURL = 'http://my.hdhomerun.com/api/recording_rules?DeviceAuth=';
	
	/*
	 * The following are the Parameters that can be included in a rule created
	 * on the SiliconDust HDHR DVR
	 * There can be a few different Combinations
	 * One Time Recordings:
	 *   - Does Not contain a Priority
	 *   - Contains a ChannelOnly Parameter
	 *   - Contains a DateTimeOnly Parameter
	 * Series Recordings
	 *   - Always contains a Priority
	 *   - Always contains a SeriesID
	 *   - Option can contain a ChannelOnly parameter
	 *   - Option can contain a RecentOnly parameter 
	 * Sports and Movies
	 *   - As Series Recordings
	 *   - Sports can optionally contain a TeamOnly parameter
	 * All Recordings
	 *   - contain a RecordingRulesID
	 *   - contain a Title
	 *   - contain an ImageURL
	 *   - contain a Synopsis
	 *   - contain a StartPadding
	 *   - contain an EndPadding
	 * Unknown
	 *   - An option is there for AfterOriginalAirdateOnly. No Idea why though
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
	
	private $rules_list = array();

	private $auth = '';
	
	public function DVRUI_Rules($hdhr) {
		//build up Auth string
		$auth='';
		
		$devices =  $hdhr->device_count();
		for ($i=0; $i < $devices; $i++) {
			$auth .= $hdhr->get_device_auth($i);
		}
		$this->auth = $auth;
	}
	
	public function processAllRules() {
		if (in_array('curl', get_loaded_extensions())){
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $this->recordingsURL . $this->auth);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_TIMEOUT, 2);
			$rules_json = curl_exec($ch);
			curl_close($ch);
		} else { 
			$context = stream_context_create(
				array('http' => array(
					'header'=>'Connection: close\r\n',
					'timeout' => 2.0)));
			$rules_json = file_get_contents($this->recordingsURL . $this->auth,false,$context);	
		}
		$rules_info = json_decode($rules_json, true);
		for ($i = 0; $i < count($rules_info); $i++) {
			$this->processRule($rules_info[$i]);
		}
	}
	
	public function processRuleforSeries($seriesID){
		if (in_array('curl', get_loaded_extensions())){
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $this->recordingsURL . $this->auth . '&SeriesID=' . $seriesID);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_TIMEOUT, 2);
			$rules_json = curl_exec($ch);
			curl_close($ch);
		} else { 
			$context = stream_context_create(
				array('http' => array(
					'header'=>'Connection: close\r\n',
					'timeout' => 2.0)));
			$rules_json = file_get_contents($this->recordingsURL . $this->auth . '&SeriesID=' . $seriesID,false,$context);	
		}
		$rules_info = json_decode($rules_json, true);
		for ($i = 0; $i < count($rules_info); $i++) {
			$this->processRule($rules_info[$i]);
		}
	}

	private function processRule($rule) {
		$recID = $rule[$this->recording_RecID];
		$seriesID = $rule[$this->recording_SeriesID];
		$title = $rule[$this->recording_Title];
		$startPad = $rule[$this->recording_StartPad];
		$endPad = $rule[$this->recording_EndPad];
		$priority = 'X';
		$dateTimeOnly = 'X';
		$channelOnly = 'All Channels';
		$teamOnly = 'X';
		$recentOnly = 'X';
		$airdate = 'X';
		$synopsis = "";
		$image = "";
	
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
	
	public function getRuleCount() {
		return count($this->rules);
	}
	
	public function getAuth() {
		return $this->auth;
	}
	
	public function getRulePriority($pos) {
		return $this->rules[$pos][$this->recording_Priority];
	}
	
	public function getRuleRecID($pos) {
		return $this->rules[$pos][$this->recording_RecID];
	}
	
	public function getRuleSeriesID($pos) {
		return $this->rules[$pos][$this->recording_SeriesID];
	}
	
	public function getRuleTitle($pos) {
		return $this->rules[$pos][$this->recording_Title];
	}
	
	public function getRuleImage($pos) {
		return $this->rules[$pos][$this->recording_ImageURL];
	}
	
	public function getRuleSynopsis($pos) {
		return $this->rules[$pos][$this->recording_Synopsis];
	}
	
	public function getRuleStartPad($pos) {
		return $this->rules[$pos][$this->recording_StartPad];
	}
	
	public function getRuleEndPad($pos) {
		return $this->rules[$pos][$this->recording_EndPad];
	}
	
	public function getRuleChannels($pos) {
		return $this->rules[$pos][$this->recording_Channel];
	}
	
	public function getRuleRecent($pos) {
		$recent = $this->rules[$pos][$this->recording_Recent];
		if ($recent == 'X'){
			return 'All Episodes';
		} else {
			return 'Recent Only';
		}
		return $this->rules[$pos][$this->recording_Recent];
	}
	
	public function getRuleDeleteURL($pos) {
		return $this->recordingsURL 
			. $this->auth 
			. $this->recordingCmd_delete
			. "&RecordingRuleID=" 
			.  $this->rules[$pos][$this->recording_RecID];
	}
	
	public function getRuleString($pos) {
		$rule = $this->rules[$pos];
		return 'Priority: ' . $rule[$this->recording_Priority]
			. ' RecID: ' . $rule[$this->recording_RecID]
			. ' SeriesID: ' . $rule[$this->recording_SeriesID]
			. ' Title: ' . $rule[$this->recording_Title]
			. ' StartPadding: ' . $rule[$this->recording_StartPad]
			. ' EndPadding: ' . $rule[$this->recording_EndPad];
	}
	
}
?>

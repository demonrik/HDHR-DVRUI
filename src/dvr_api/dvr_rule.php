<?php
require_once("common/common.php");

class dvr_rule {
	/*
	 * Documentation on How the HDHR system exposes Recording rules is explained
	 * https://github.com/Silicondust/documentation/wiki/DVR%20Recording%20Rules
	 */

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
	 *   - An option is there for AfterOriginalAirdateOnly. 
	 *		 restricts recordings to first-airings or shows with original airdate >= given date
	 */
	 	 
	/* general series rule parameters */
	private $rule_RecID = 'RecordingRuleID';
	private $rule_SeriesID = 'SeriesID';
	private $rule_Title = 'Title';
	private $rule_Synopsis = 'Synopsis';
	private $rule_Category = 'Category';
	private $rule_ImageURL = 'ImageURL';
	private $rule_Priority = 'Priority';
	private $rule_StartPad = 'StartPadding';
	private $rule_EndPad = 'EndPadding';
	
	/* Advanced series rule parameters */
	private $rule_Airdate = 'AfterOriginalAirdateOnly';
	private $rule_Recent = 'RecentOnly';
	private $rule_Channel = 'ChannelOnly';
	private $rule_DateTimeOnly = 'DateTimeOnly';

	/* Advanced sports rule parameters */
	private $rule_Team = 'TeamOnly';

	/* Rule commands */
	private $ruleCmd_delete = '&Cmd=delete';
	private $ruleCmd_change = '&Cmd=change';
	private $ruleCmd_add = '&Cmd=add';

	/* param to change priority of rule */
	private $rule_AfterRecordingID = 'AfterRecordingRuleID';

	
	private $rule = array();
	
	public function __construct($rule) {
		$this->processRule($rule);
	}

	public function dvr_rule($rule) {
		self::__construct($rule);
	}

	private function processRule($rule) {
		$ruleID = 0;
		$seriesID = 0;
		$title = '';
		$synopsis = "";
		$category = "";
		$startPad = 0;
		$endPad = 0;
		$priority = 'X';
		$image = NO_IMAGE;

		$dateTimeOnly = '';
		$channelOnly = 'All Channels';
		$teamOnly = 'X';
		$recentOnly = 'X';
		$airdate = 'X';

		error_log("Processing Rule.... " . print_r($rule, true));
			
		if (array_key_exists($this->rule_RecID,$rule)){
			$recID = $rule[$this->rule_RecID];
		}
		if (array_key_exists($this->rule_SeriesID,$rule)){
			$seriesID = $rule[$this->rule_SeriesID];
		}
		if (array_key_exists($this->rule_Title,$rule)){
			$title = $rule[$this->rule_Title];
		}
		if (array_key_exists($this->rule_Category,$rule)){
			$category = $rule[$this->rule_Category];
		}
		if (array_key_exists($this->rule_ImageURL,$rule)){
			$image = $rule[$this->rule_ImageURL];
		}
		if (array_key_exists($this->rule_Synopsis,$rule)){
			$synopsis = $rule[$this->rule_Synopsis];
		}
		if (array_key_exists($this->rule_Priority,$rule)){
			$priority = $rule[$this->rule_Priority];
		}
		if (array_key_exists($this->rule_StartPad,$rule)) {
			$startPad = $rule[$this->rule_StartPad];
		}
		if (array_key_exists($this->rule_EndPad,$rule)) {
			$endPad = $rule[$this->rule_EndPad];
		}

		if (array_key_exists($this->rule_DateTimeOnly,$rule)){
			$dateTimeOnly = $rule[$this->rule_DateTimeOnly];
		}
		if (array_key_exists($this->rule_Channel,$rule)) {
			$channelOnly = 'Channel' . $rule[$this->rule_Channel];
		}
		if (array_key_exists($this->rule_Team,$rule)) {
			$teamOnly = $rule[$this->rule_Team];
		}
		if (array_key_exists($this->rule_Recent,$rule)) {
			$recentOnly = $rule[$this->rule_Recent];
		}
		if (array_key_exists($this->rule_Airdate,$rule)) {
			$airdate = $rule[$this->rule_Airdate];
		}
			
		$this->rule = array($this->rule_RecID => $recID,
				$this->rule_SeriesID => $seriesID,
				$this->rule_ImageURL => $image,
				$this->rule_Title => $title,
				$this->rule_Synopsis => $synopsis,
				$this->rule_Category => $category,
				$this->rule_StartPad => $startPad,
				$this->rule_EndPad => $endPad,
				$this->rule_Priority => $priority,
				$this->rule_DateTimeOnly => $dateTimeOnly,
				$this->rule_Channel => $channelOnly,
				$this->rule_Team => $teamOnly,
				$this->rule_Recent => $recentOnly,
				$this->rule_Airdate => $airdate);
	}

	public function get_rule_RecID(){
		return $this->rule[$this->rule_RecID];
	}
	public function get_rule_SeriesID(){
		return $this->rule[$this->rule_SeriesID];
	}
	public function get_rule_ImageURL(){
		return $this->rule[$this->rule_ImageURL];
	}
	public function get_rule_Title(){
		return $this->rule[$this->rule_Title];
	}
	public function get_rule_Synopsis(){
		return $this->rule[$this->rule_Synopsis];
	}
	public function get_rule_Category(){
		return $this->rule[$this->rule_Category];
	}
	public function get_rule_StartPad(){
		return $this->rule[$this->rule_StartPad];
	}
	public function get_rule_EndPad(){
		return $this->rule[$this->rule_EndPad];
	}
	public function get_rule_Priority(){
		return $this->rule[$this->rule_Priority];
	}
	public function get_rule_DateTimeOnly(){
		return $this->rule[$this->rule_DateTimeOnly];
	}
	public function get_rule_Channel(){
		return $this->rule[$this->rule_Channel];
	}
	public function get_rule_Team(){
		return $this->rule[$this->rule_Team];
	}
	public function get_rule_Recent(){
		return $this->rule[$this->rule_Recent];
	}
	public function get_rule_Airdate(){
		return $this->rule[$this->rule_Airdate];
	}
	
}

?>
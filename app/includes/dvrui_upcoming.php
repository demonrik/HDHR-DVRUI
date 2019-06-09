<?php
	require_once("includes/dvrui_rules.php");
	require_once("includes/dvrui_common.php");
	require_once("includes/dvrui_tz.php");


class DVRUI_Upcoming {
	/*
	 * Documentation on HDHR APis is available at:
	 * https://github.com/Silicondust/documentation/wiki
	 * Note: At this time the guide APIs are not published, so these are likely
	 *       to change at some point in the future
	 */
	private $epGuideURL =  '';
	private $epGuideURL_paramAuth = 'DeviceAuth=';
	private $epGuideURL_paramSeries = '&SeriesID=';
	
	private $epData_SeriesID = 'SeriesID';
	private $epData_RecordingRule = 'RecordingRule';
	private $epData_ProgramID = 'ProgramID';
	private $epData_Title = 'Title';
	private $epData_EpisodeNumber = 'EpisodeNumber';
	private $epData_EpisodeTitle = 'EpisodeTitle';
	private $epData_Synopsis = 'Synopsis';
	private $epData_ImageURL = 'ImageURL';
	private $epData_OriginalAirDate = 'OriginalAirdate';
	private $epData_StartTime = 'StartTime';
	private $epData_EndTime = 'EndTime';
	private $epData_ChannelImageURL = 'ChannelImageURL';
	private $epData_ChannelName = 'ChannelName';
	private $epData_ChannelNumber = 'ChannelNumber';
	private $cachesecs = 3600;	
	private	$upcoming_list = array();
	private	$series_list = array();
	private	$recordings = NULL;
	private $auth = '';
	
	public function DVRUI_Upcoming($hdhr, $recordings) {
		DVRUI_setTZ();
		$this->epGuideURL =  DVRUI_Vars::DVRUI_apiurl . 'api/episodes?';
    $this->auth = $hdhr->get_auth();
    $this->recordings = $recordings;
		if(DVRUI_Vars::DVRUI_upcoming_cache != ''){
			$this->cachesecs = DVRUI_Vars::DVRUI_upcoming_cache;
		}

	}
	public function initByRules($rules) {
		$series = array();
		// only interested in small set of the rules data
		for ($i=0; $i < $rules->getRuleCount(); $i++) {
			if(!isset($series[$rules->getRuleSeriesID($i)])){
				$series[$rules->getRuleSeriesID($i)] = $rules->getRuleSeriesID($i);
				$this->series_list[] = array(
					$this->epData_SeriesID => $rules->getRuleSeriesID($i),
					$this->epData_Title => $rules->getRuleTitle($i));
			}
		}
	}

	public function initBySeries($seriesID) {
		// only interested in one series
		$this->series_list[] = array(
			$this->epData_SeriesID => $seriesID,
			$this->epData_Title => $seriesID);
		$this->processNext(0);
	}
	
	private function extractEpisodeInfo($episode){
		$programID = '';
		$title = '';
		$episodeNumber = '';
		$episodeTitle = '';
		$synopsis = '';
		$imageURL = NO_IMAGE;
		$originalAirDate = '';
		$startTime = '';
		$endTime = '';
		$channelImageURL = '';
		$channelName = '';
		$channelNumber = '';
		$originalAirDate = '';

		$recordingRule = $episode[$this->epData_RecordingRule];

		if (array_key_exists($this->epData_ProgramID,$episode)){
			$programID = $episode[$this->epData_ProgramID];
		}
		if (array_key_exists($this->epData_Title,$episode)){
			$title = $episode[$this->epData_Title];
		}
		if (array_key_exists($this->epData_OriginalAirDate,$episode)){
			$originalAirDate = $episode[$this->epData_OriginalAirDate];
		}
		if (array_key_exists($this->epData_EpisodeNumber,$episode)){
			$episodeNumber = $episode[$this->epData_EpisodeNumber];
		}
		if (array_key_exists($this->epData_EpisodeTitle,$episode)){
			$episodeTitle = $episode[$this->epData_EpisodeTitle];
		}
		if (array_key_exists($this->epData_StartTime,$episode)){
			$startTime = $episode[$this->epData_StartTime];
		}
		if (array_key_exists($this->epData_EndTime,$episode)){
			$endTime = $episode[$this->epData_EndTime];
		}
		if (array_key_exists($this->epData_ChannelName,$episode)){
			$channelName = $episode[$this->epData_ChannelName];
		}
		if (array_key_exists($this->epData_ChannelNumber,$episode)){
			$channelNumber = $episode[$this->epData_ChannelNumber];
		}
		if (array_key_exists($this->epData_Synopsis,$episode)){
			$synopsis = $episode[$this->epData_Synopsis];
		}
		if (array_key_exists($this->epData_ImageURL,$episode)) {
			$imageURL = $episode[$this->epData_ImageURL];
		}
		$this->upcoming_list[] = array(
			$this->epData_ProgramID => $programID,
			$this->epData_Title => $title,
			$this->epData_EpisodeNumber => $episodeNumber,
			$this->epData_OriginalAirDate => $originalAirDate,
			$this->epData_EpisodeTitle => $episodeTitle,
			$this->epData_StartTime => $startTime,
			$this->epData_EndTime => $endTime,
			$this->epData_ImageURL => $imageURL,
			$this->epData_Synopsis => $synopsis,
			$this->epData_ChannelName => $channelName,
			$this->epData_ChannelNumber => $channelNumber,
			$this->epData_RecordingRule => $recordingRule);
	}
	
	public function getSeriesCount() {
		return count($this->series_list);
	}

	public function getUpcomingCount() {
		return count($this->upcoming_list);
	}

	public function deleteCachedUpcoming($seriesid){
		$seriesURL = $this->epGuideURL . 
			$this->epGuideURL_paramAuth . 
			$this->auth . 
			$this->epGuideURL_paramSeries .
			$seriesid;
		clearCacheFile($seriesURL);
	}	
	
	private function isDuplicate($episode) {
		for ($i=0; $i < count($this->upcoming_list); $i++) {
			if ($this->upcoming_list[$i][$this->epData_ProgramID] == $episode[$this->epData_ProgramID]) {
				return TRUE;
			}
		}
		return FALSE;
	}
	
	private function alreadyExists($episode) {
		if ($this->recordings != NULL) {
			return $this->recordings->verifyExistsByProgramID($episode[$this->epData_ProgramID]);
		}
		return FALSE;	
	}

	private function isIgnoredByRule($rule,$episode) {
		return FALSE;	
	}
	
	public function processNext($pos) {
		if (count($this->series_list) > 0) {
			$seriesURL = $this->epGuideURL . 
						$this->epGuideURL_paramAuth . 
						$this->auth . 
						$this->epGuideURL_paramSeries .
						$this->series_list[$pos][$this->epData_SeriesID];

			$episodes_info = getCachedJsonFromUrl($seriesURL,$this->cachesecs);
			
			for ($i = 0; $i < count($episodes_info); $i++) {
				if (array_key_exists($this->epData_RecordingRule,$episodes_info[$i])){
					if ((!$this->isDuplicate($episodes_info[$i])) 
						&& (!$this->alreadyExists($episodes_info[$i]))){
							$this->extractEpisodeInfo($episodes_info[$i]);
						}
				}
			}
		}
	}
	
	public function sortUpcomingByDate(){
		usort($this->upcoming_list, function ($a, $b) {
			if ($a[$this->epData_StartTime] == $b[$this->epData_StartTime]) {
				return 0;
			}
			else {
				return ($a[$this->epData_StartTime] < $b[$this->epData_StartTime]) ? -1 : 1;
			}
		});
		return;
	}
	
	public function sortUpcomingByTitle(){
		usort($this->upcoming_list, function ($a, $b) {
			if ($a[$this->epData_Title] == $b[$this->epData_Title]) {
				return 0;
			}
			else {
				return ($a[$this->epData_Title] < $b[$this->epData_Title]) ? -1 : 1;
			}
		});
		return;
	}

	public function countByDate($timestamp){
		$epcount = 0;
		$listcount = count($this->upcoming_list);
		$selecteddate = date_format($timestamp,'M/d/Y');
		for($c = 0; $c < $listcount; $c++){
			$listdate = date('M/d/Y',$this->upcoming_list[$c][$this->epData_StartTime]);
			if($listdate == $selecteddate){
				++$epcount;
			}
		}
		return $epcount;
	}
	
	public function getTitle($pos) {
		if ($pos < count($this->upcoming_list)) {
			return $this->upcoming_list[$pos][$this->epData_Title];
		} else {
			return '';
		}
	}
	public function getEpNum($pos) {
		if ($pos < count($this->upcoming_list)) {
			return $this->upcoming_list[$pos][$this->epData_EpisodeNumber];
		} else {
			return '';
		}
	}
	
	public function getEpTitle($pos) {
		if ($pos < count($this->upcoming_list)) {
			return $this->upcoming_list[$pos][$this->epData_EpisodeTitle];
		} else {
			return '';
		}
	}
	public function getEpOriginalAirDate($pos) {
		if ($pos < count($this->upcoming_list)) {
			return date('D M/d Y',$this->upcoming_list[$pos][$this->epData_OriginalAirDate]);
		} else {
			return '';
		}
	}
	
	public function getEpStartShort($pos) {
		if ($pos < count($this->upcoming_list)) {
			return date('D M d',$this->upcoming_list[$pos][$this->epData_StartTime]);
		} else {
			return '';
		}
	}

	public function getEpStart($pos) {
		if ($pos < count($this->upcoming_list)) {
			return date('D M/d Y @ g:ia T',$this->upcoming_list[$pos][$this->epData_StartTime]);
		} else {
			return '';
		}
	}
	
	public function getEpEnd($pos) {
		if ($pos < count($this->upcoming_list)) {
			return  date('D M/d Y @ g:ia T',$this->upcoming_list[$pos][$this->epData_EndTime]);
		} else {
			return '';
		}
	}
	
	public function getEpChannelNum($pos) {
		if ($pos < count($this->upcoming_list)) {
			return $this->upcoming_list[$pos][$this->epData_ChannelNumber];
		} else {
			return '';
		}
	}
	
	public function getEpChannelName($pos) {
		if ($pos < count($this->upcoming_list)) {
			return $this->upcoming_list[$pos][$this->epData_ChannelName];
		} else {
			return '';
		}
	}

	public function getEpImg($pos) {
		if ($pos < count($this->upcoming_list)) {
			return $this->upcoming_list[$pos][$this->epData_ImageURL];
		} else {
			return '';
		}
	}

	public function getEpSynopsis($pos) {
		if ($pos < count($this->upcoming_list)) {
			return $this->upcoming_list[$pos][$this->epData_Synopsis];
		} else {
			return '';
		}
	}
	
}
?>

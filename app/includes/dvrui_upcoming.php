<?php
	require_once("includes/dvrui_rules.php");

class DVRUI_Upcoming {
	/*
	 * Documentation on HDHR APis is available at:
	 * https://github.com/Silicondust/documentation/wiki
	 * Note: At this time the guide APIs are not published, so these are likely
	 *       to change at some point in the future
	 */
	private $epGuideURL = 'http://my.hdhomerun.com/api/episodes?';
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
	private $epData_OriginalAirDate = 'OriginalAirDate';
	private $epData_StartTime = 'StartTime';
	private $epData_EndTime = 'EndTime';
	private $epData_ChannelImageURL = 'ChannelImageURL';
	private $epData_ChannelName = 'ChannelName';
	private $epData_ChannelNumber = 'ChannelNumber';
	
	private	$upcoming_list = array();
	private	$series_list = array();
	private $auth = '';

	public function DVRUI_Upcoming($rules) {
		// only interested in small set of the rules data
		$this->auth = $rules->getAuth();
		for ($i=0; $i < $rules->getRuleCount(); $i++) {
			$this->series_list[] = array(
				$this->epData_SeriesID => $rules->getRuleSeriesID($i),
				$this->epData_Title => $rules->getRuleTitle($i));
		}
	}
	
	private function extractEpisodeInfo($episode){
		$programID = '';
		$title = '';
		$episodeNumber = '';
		$episodeTitle = '';
		$synopsis = '';
		$imageURL = '';
		$originalAirDate = '';
		$startTime = '';
		$endTime = '';
		$channelImageURL = '';
		$channelName = '';
		$channelNumber = '';
		$recordingRule = $episode[$this->epData_RecordingRule];

		if (array_key_exists($this->epData_ProgramID,$episode)){
			$programID = $episode[$this->epData_ProgramID];
		}
		if (array_key_exists($this->epData_Title,$episode)){
			$title = $episode[$this->epData_Title];
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
		if (array_key_exists($this->epData_ImageURL,$episode)){
			$imageURL = $episode[$this->epData_ImageURL];
		}
		$this->upcoming_list[] = array(
			$this->epData_ProgramID => $programID,
			$this->epData_Title => $title,
			$this->epData_EpisodeNumber => $episodeNumber,
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
	
	public function processNext($pos) {
		if (count($this->series_list) > 0) {
			$seriesURL = $this->epGuideURL . 
						$this->epGuideURL_paramAuth . 
						$this->auth . 
						$this->epGuideURL_paramSeries .
						$this->series_list[$pos][$this->epData_SeriesID];
			if (in_array('curl', get_loaded_extensions())){
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, $seriesURL);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($ch, CURLOPT_TIMEOUT, 2);
				$episodes_json = curl_exec($ch);
				curl_close($ch);
			} else { 
				$context = stream_context_create(
					array('http' => array(
						'header'=>'Connection: close\r\n',
						'timeout' => 2.0)));
				$episodes_json = file_get_contents($this->recordingsURL . $auth,false,$context);	
			}
			$episodes_info = json_decode($episodes_json, true);
			
			for ($i = 0; $i < count($episodes_info); $i++) {
				if (array_key_exists($this->epData_RecordingRule,$episodes_info[$i])){
					$this->extractEpisodeInfo($episodes_info[$i]);
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
		return;
	}
	
	public function getUpcomingEpInfo($pos) {
		date_default_timezone_set('UTC');
		if ($pos < count($this->upcoming_list)) {
			$episode = $this->upcoming_list[$pos];
			return 'ProgramID: ' . $episode[$this->epData_ProgramID]
				. ' Title: ' . $episode[$this->epData_Title]
				. ' epNum: ' . $episode[$this->epData_EpisodeNumber]
				. ' epTitle: ' . $episode[$this->epData_EpisodeTitle]
				. ' StartTime: ' . date('D M/d Y @ g:ia T',$episode[$this->epData_StartTime])
				. ' EndTime: ' . date('D M/d Y @ g:ia',$episode[$this->epData_EndTime]);
		} else {
			return '';
		}
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
	
	public function getEpStart($pos) {
		date_default_timezone_set('UTC');
		if ($pos < count($this->upcoming_list)) {
			return date('D M/d Y @ g:ia T',$this->upcoming_list[$pos][$this->epData_StartTime]);
		} else {
			return '';
		}
	}
	
	public function getEpEnd($pos) {
		date_default_timezone_set('UTC');
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

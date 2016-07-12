<?php
	require_once("includes/dvrui_hdhrjson.php");

class DVRUI_Search {
	private $search_SeriesID = 'SeriesID';
	private $search_Title = 'Title';
	private $search_ImageURL = 'ImageURL';
	private $search_Synopsis = 'Synopsis';
	private $search_ChannelNumber = 'ChannelNumber';
	private $search_ChannelName = 'ChannelName';
	private $search_ChannelImageURL = 'ChannelImageURL';
	private $search_RecordingRule = 'RecordingRule';
	private $search_OriginalAirDate = 'OriginalAirdate';
	private $searchResults = array();
	
	private $search_list = array();

	private $auth = '';
	private $searchURL = "http://my.hdhomerun.com/api/search?DeviceAuth=";
	
	public function DVRUI_Search($hdhr, $searchString) {

		//build up Auth string
		$auth='';
		
		$devices =  $hdhr->device_count();
		for ($i=0; $i < $devices; $i++) {
			$auth .= $hdhr->get_device_auth($i);
		}
		$this->auth = $auth;

		if (in_array('curl', get_loaded_extensions())){
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $this->searchURL . $auth . "&Search=" . $searchString);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_TIMEOUT, 2);
			$search_json = curl_exec($ch);
			curl_close($ch);
		} else { 
			$context = stream_context_create(
				array('http' => array(
					'header'=>'Connection: close\r\n',
					'timeout' => 2.0)));
			$search_json = file_get_contents($this->searchURL . $auth . "&Search=" . $searchString,false,$context);	
		}
		$search_info = json_decode($search_json, true);
		for ($i = 0; $i < count($search_info); $i++) {
			$seriesID = $search_info[$i][$this->search_SeriesID];
			$image = "";
			$title = $search_info[$i][$this->search_Title];
			$originalAirDate = 0;	
			$recordingRule = 0;	
			if (array_key_exists($this->search_ImageURL,$search_info[$i])){
				$image = $search_info[$i][$this->search_ImageURL];
			}
			if (array_key_exists($this->search_Synopsis,$search_info[$i])){
				$synopsis = $search_info[$i][$this->search_Synopsis];
			}
			if (array_key_exists($this->search_ChannelNumber,$search_info[$i])) {
				$channelNumber = $search_info[$i][$this->search_ChannelNumber];
			}
			if (array_key_exists($this->search_ChannelName,$search_info[$i])) {
				$channelName = $search_info[$i][$this->search_ChannelName];
			}
			if (array_key_exists($this->search_ChannelImageURL,$search_info[$i])) {
				$channelImageURL = $search_info[$i][$this->search_ChannelImageURL];
			}
			if (array_key_exists($this->search_OriginalAirDate,$search_info[$i])) {
				$originalAirDate = $search_info[$i][$this->search_OriginalAirDate];
			}
			if (array_key_exists($this->search_RecordingRule,$search_info[$i])) {
				$recordingRule = $search_info[$i][$this->search_RecordingRule];
			}


			
			$this->searchResults[] = array(
					$this->search_SeriesID => $seriesID,
					$this->search_ImageURL => $image,
					$this->search_Title => $title,
					$this->search_Synopsis => $synopsis,
					$this->search_ChannelNumber => $channelNumber,
					$this->search_ChannelName => $channelName,
					$this->search_ChannelImageURL => $channelImageURL,
					$this->search_OriginalAirDate => $originalAirDate,
					$this->search_RecordingRule => $recordingRule	
		
				);
		}
		
	}

	public function getSearchResultCount() {
		return count($this->searchResults);
	}
	
	public function getAuth() {
		return $this->auth;
	}
	
	public function getSearchResultSeriesID($pos) {
		return $this->searchResults[$pos][$this->search_SeriesID];
	}
	public function getSearchResultTitle($pos) {
		return $this->searchResults[$pos][$this->search_Title];
	}
	public function getSearchResultImage($pos) {
		return $this->searchResults[$pos][$this->search_ImageURL];
	}
	public function getSearchResultSynopsis($pos) {
		return $this->searchResults[$pos][$this->search_Synopsis];
	}
	public function getSearchResultChannelNumber($pos) {
		return $this->searchResults[$pos][$this->search_ChannelNumber];
	}
	public function getSearchResultChannelName($pos) {
		return $this->searchResults[$pos][$this->search_ChannelName];
	}
	public function getSearchResultChannelImageURL($pos) {
		return $this->searchResults[$pos][$this->search_ChannelImageURL];
	}
	public function getSearchResultOriginalAirDate($pos) {
		
		if($this->searchResults[$pos][$this->search_OriginalAirDate] == 0){
			return "N/A";
		}else{
			return date("m/d/Y",$this->searchResults[$pos][$this->search_OriginalAirDate]);
		}
	}
	public function getSearchResultRecordingRules($pos) {
		return $this->searchResults[$pos][$this->search_RecordingRule];
	}
	
	public function getRecordRecentURL($pos) {
		return "https://my.hdhomerun.com/api/recording_rules?DeviceAuth=" . $this->auth . "&SeriesID=" .  $this->searchResults[$pos][$this->search_SeriesID] . '&Cmd=add&RecentOnly=1';
	}
	public function getRecordAllURL($pos) {
		return "https://my.hdhomerun.com/api/recording_rules?DeviceAuth=" . $this->auth . "&SeriesID=" .  $this->searchResults[$pos][$this->search_SeriesID] . '&Cmd=add&RecentOnly=0';
	}
}
?>

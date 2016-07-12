<?php
	require_once("includes/dvrui_hdhrjson.php");

class DVRUI_Recordings {
	
	
	private $recording_Category = 'Category';
	private $recording_ChannelImageURL = 'ChannelImageURL';
	private $recording_ChannelName = 'ChannelName';
	private $recording_ChannelNumber = 'ChannelNumber';
	private $recording_EpisodeNumber = 'EpisodeNumber';
	private $recording_ImageURL = 'ImageURL';
	private $recording_EpisodeTitle = 'EpisodeTitle';
	private $recording_OriginalAirDate = 'OriginalAirDate';
	private $recording_RecordStartTime = 'RecordStartTime';
	private $recording_Synopsis = 'Synopsis';
	private $recording_Title = 'Title';
	private $recording_DisplayGroupTitle = 'DisplayGroupTitle';
	private $recording_PlayURL = 'PlayURL';
	private $recording_CmdURL = 'CmdURL';

	
	private $recordings_list = array();

	public function DVRUI_Recordings($hdhr) {
		$this->recordingsURL = $hdhr->get_storage_url();	
	
		if (in_array('curl', get_loaded_extensions())){
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $this->recordingsURL);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_TIMEOUT, 2);
			$recordings_json = curl_exec($ch);
			curl_close($ch);
		} else { 
			$context = stream_context_create(
				array('http' => array(
					'header'=>'Connection: close\r\n',
					'timeout' => 2.0)));
			$recordings_json = file_get_contents($this->recordingsURL,false,$context);	
		}
		$recordings_info = json_decode($recordings_json, true);
		for ($i = 0; $i < count($recordings_info); $i++) {
				$playURL = $recordings_info[$i][$this->recording_PlayURL];
				$cmdURL = $recordings_info[$i][$this->recording_CmdURL];
				$displayGroupTitle = $recordings_info[$i][$this->recording_DisplayGroupTitle];
				$category = '';
				$channelImageURL = '';
				$channelName = '';
				$channelNumber = '';
				$episodeNumber = '';
				$imageURL = '';
				$episodeTitle = '';
				$originalAirDate = '';
				$recordStartTime = '';
				$synopsis = '';
				$title = '';


				if (array_key_exists($this->recording_Category,$recordings_info[$i])){
					$category = $recordings_info[$i][$this->recording_Category];
				}
				if (array_key_exists($this->recording_ChannelImageURL,$recordings_info[$i])){
					$channelImageURL = $recordings_info[$i][$this->recording_ChannelImageURL];
				}
				if (array_key_exists($this->recording_ChannelName,$recordings_info[$i])){
					$channelName = $recordings_info[$i][$this->recording_ChannelName];
				}
				if (array_key_exists($this->recording_ChannelNumber,$recordings_info[$i])){
					$channelNumber = $recordings_info[$i][$this->recording_ChannelNumber];
				}
				if (array_key_exists($this->recording_EpisodeNumber,$recordings_info[$i])){
					$episodeNumber = $recordings_info[$i][$this->recording_EpisodeNumber];
				}
				if (array_key_exists($this->recording_ImageURL,$recordings_info[$i])){
					$imageURL = $recordings_info[$i][$this->recording_ImageURL];
				}
				if (array_key_exists($this->recording_EpisodeTitle,$recordings_info[$i])){
					$episodeTitle = $recordings_info[$i][$this->recording_EpisodeTitle];
				}
				if (array_key_exists($this->recording_OriginalAirDate,$recordings_info[$i])){
					$originalAirDate = $recordings_info[$i][$this->recording_OriginalAirDate];
				}
				if (array_key_exists($this->recording_RecordStartTime,$recordings_info[$i])){
					$recordStartTime = $recordings_info[$i][$this->recording_RecordStartTime];
				}
				if (array_key_exists($this->recording_Synopsis,$recordings_info[$i])){
					$synopsis = $recordings_info[$i][$this->recording_Synopsis];
				}
				if (array_key_exists($this->recording_Title,$recordings_info[$i])){
					$title = $recordings_info[$i][$this->recording_Title];
				}

				$this->recordings[] = array(
					$this->recording_PlayURL => $playURL,
					$this->recording_CmdURL => $cmdURL,
					$this->recording_DisplayGroupTitle => $displayGroupTitle,
					$this->recording_Category => $category,
					$this->recording_ChannelImageURL => $channelImageURL,
					$this->recording_ChannelName => $channelName,
					$this->recording_ChannelNumber => $channelNumber,
					$this->recording_EpisodeNumber => $episodeNumber,
					$this->recording_ImageURL => $imageURL,
					$this->recording_EpisodeTitle => $episodeTitle,
					$this->recording_OriginalAirDate => $originalAirDate,
					$this->recording_RecordStartTime => $recordStartTime,
					$this->recording_Synopsis => $synopsis,
					$this->recording_Title => $title);


		}
	}

	public function getRecordingCount() {
		return count($this->recordings);
	}

        public function getDisplayGroupTitle($pos) {
                return $this->recordings[$pos][$this->recording_DisplayGroupTitle];
	}

        public function get_PlayURL($pos) {
                return $this->recordings[$pos][$this->recording_PlayURL];
        }

        public function get_CmdURL($pos) {
                return $this->recordings[$pos][$this->recording_CmdURL];
        }

        public function getRecordingImage($pos) {
                return $this->recordings[$pos][$this->recording_ImageURL];
        }

        public function getCategory($pos) {
                return $this->recordings[$pos][$this->recording_Category];
        }

        public function getChannelImageURL($pos) {
                return $this->recordings[$pos][$this->recording_ChannelImageURL];
        }

        public function getChannelName($pos) {
                return $this->recordings[$pos][$this->recording_ChannelName];
        }

        public function getChannelNumber($pos) {
                return $this->recordings[$pos][$this->recording_ChannelNumber];
        }

	public function getEpisodeNumber($pos) {
		if ($this->recordings[$pos][$this->recording_EpisodeNumber] == 'X') {
			return '';
		} else {
			return $this->recordings[$pos][$this->recording_EpisodeNumber];
		}
	}

	public function getEpisodeTitle($pos) {
		if ($this->recordings[$pos][$this->recording_EpisodeTitle] == 'X') {
			return '';
		} else {
			return $this->recordings[$pos][$this->recording_EpisodeTitle];
		}
	}

        public function getOriginalAirDate($pos) {
                return $this->recordings[$pos][$this->recording_OriginalAirDate];
        }

        public function getRecordStartTime($pos) {
                return $this->recordings[$pos][$this->recording_RecordStartTime];
        }

        public function getSynopsis($pos) {
                return $this->recordings[$pos][$this->recording_Synopsis];
        }

        public function getTitle($pos) {
                return $this->recordings[$pos][$this->recording_Title];
        }

	public function getDeleteCmdURL($pos) {
		return $this->recordings[$pos][$this->recording_CmdURL] . '&cmd=delete&rerecord=0';
	}

	public function getRerecordCmdURL($pos) {
		return $this->recordings[$pos][$this->recording_CmdURL] . '&cmd=delete&rerecord=1';
	}
        
	public function getLinks($pos) {
		return  '[<a href="' . $this->recordings[$pos][$this->recording_PlayURL] . '">Play</a>] ' .
			'[<a href="' . $this->recordings[$pos][$this->recording_CmdURL] . '&cmd=delete&rerecord=0" target=new>Del</a>] ' .
			'[<a href="' . $this->recordings[$pos][$this->recording_CmdURL] . '&cmd=delete&rerecord=1" target=new>Rerecord</a>] ' ;
        }


	public function getRecordingString($pos) {
		$recording = $this->recordings[$pos];
		return '<tr><td>' .
			'[<a href="' . $recording[$this->recording_PlayURL] . '">Play</a>] ' .
			'[<a href="' . $recording[$this->recording_CmdURL]  . '&cmd=delete&rerecord=0" target=new>Delete</a>] ' .
			'[<a href="' . $recording[$this->recording_CmdURL] . '&cmd=delete&rerecord=1" target=new>Rerecord</a>]' .
			'</td><td>' . $recording[$this->recording_Title] .
			'</td><td>' . $recording[$this->recording_EpisodeNumber] . 
			'</td><td>' . $recording[$this->recording_EpisodeTitle] . '</td></tr>'; 
	}
	
}
?>

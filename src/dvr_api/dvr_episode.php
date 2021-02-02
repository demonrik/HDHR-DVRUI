<?php
class dvr_episode {
	private $key_title = 'Title';
	private $key_category = 'Category';
	private $key_series_id = 'SeriesID';

	private $key_chnl_affiliate = 'ChannelAffiliate';
	private $key_chnl_image_url = 'ChannelImageURL';
	private $key_chnl_name = 'ChannelName';
	private $key_chnl_number = 'ChannelNumber';

	private $key_ep_number = 'EpisodeNumber';
	private $key_ep_title = 'EpisodeTitle';
	private $key_synopsis = 'Synopsis';
	private $key_filename = 'Filename';

	private $key_start_time = 'StartTime';
	private $key_end_time = 'EndTime';
	private $key_first_air = 'FirstAiring';
	private $key_image_url = 'ImageURL';
	private $key_original_airdate = 'OriginalAirdate';
	private $key_program_id = 'ProgramID';

	private $key_rec_start_time = 'RecordStartTime';
	private $key_rec_end_time = 'RecordEndTime';
	private $key_rec_success = 'RecordSuccess';
	
	private $key_play_url = 'PlayURL';
	private $key_cmd_url = 'CmdURL';


	private $title = 'Title';
	private $category = 'Category';
	private $series_id = 'SeriesID';

	private $chnl_affiliate = '';
	private $chnl_image_url = '';
	private $chnl_name = '';
	private $chnl_number = '';

	private $ep_number = '';
	private $ep_title = '';
	private $synopsis = '';
	private $filename = '';

	private $start_time = '';
	private $end_time = '';
	private $first_air = '';
	private $image_url = '';
	private $original_airdate = '';
	private $program_id = '';

	private $rec_start_time = '';
	private $rec_end_time = '';
	private $rec_success = '';
	
	private $play_url = '';
	private $cmd_url = '';


	private $episodes_count = '0';
	
	public function dvr_episode($episode_info){
		error_log("Processing Epsiode Info: ". print_r($episode_info,true));

		if (array_key_exists($this->key_image_url,$episode_info)) {
			$this->image_url = $episode_info[$this->key_image_url];
		}
		if (array_key_exists($this->key_start_time,$episode_info)) {
			$this->start_time = $episode_info[$this->key_start_time];
		}
		if (array_key_exists($this->key_end_time,$episode_info)) {
			$this->end_time = $episode_info[$this->key_end_time];
		}
		if (array_key_exists($this->key_first_air,$episode_info)) {
			$this->first_air = $episode_info[$this->key_first_air];
		}
		if (array_key_exists($this->key_original_airdate,$episode_info)) {
			$this->original_airdate = $episode_info[$this->key_original_airdate];
		}
		if (array_key_exists($this->key_program_id,$episode_info)) {
			$this->program_id = $episode_info[$this->key_program_id];
		}
		if (array_key_exists($this->key_rec_start_time,$episode_info)) {
			$this->rec_start_time = $episode_info[$this->key_rec_start_time];
		}
		if (array_key_exists($this->key_rec_end_time,$episode_info)) {
			$this->rec_end_time = $episode_info[$this->key_rec_end_time];
		}
		if (array_key_exists($this->key_rec_success,$episode_info)) {
			$this->rec_success = $episode_info[$this->key_rec_success];
		}
		if (array_key_exists($this->key_play_url,$episode_info)) {
			$this->play_url = $episode_info[$this->key_play_url];
		}
		if (array_key_exists($this->key_rec_end_time,$episode_info)) {
			$this->cmd_url = $episode_info[$this->key_cmd_url];
		}

		if (array_key_exists($this->key_title,$episode_info)) {
			$this->title = $episode_info[$this->key_title];
		}
		if (array_key_exists($this->key_category,$episode_info)) {
			$this->category = $episode_info[$this->key_category];
		}
		if (array_key_exists($this->key_series_id,$episode_info)) {
			$this->series_id = $episode_info[$this->key_series_id];
		}
		
		if (array_key_exists($this->key_chnl_affiliate,$episode_info)) {
			$this->chnl_affiliate = $episode_info[$this->key_chnl_affiliate];
		}
		if (array_key_exists($this->key_chnl_image_url,$episode_info)) {
			$this->chnl_image_url = $episode_info[$this->key_chnl_image_url];
		}
		if (array_key_exists($this->key_chnl_name,$episode_info)) {
			$this->chnl_name = $episode_info[$this->key_chnl_name];
		}
		if (array_key_exists($this->key_chnl_number,$episode_info)) {
			$this->chnl_number = $episode_info[$this->key_chnl_number];
		}

		if (array_key_exists($this->key_ep_number,$episode_info)) {
			$this->ep_number = $episode_info[$this->key_ep_number];
		}
		if (array_key_exists($this->key_ep_title,$episode_info)) {
			$this->ep_title = $episode_info[$this->key_ep_title];
		}
		if (array_key_exists($this->key_synopsis,$episode_info)) {
			$this->synopsis = $episode_info[$this->key_synopsis];
		}
		if (array_key_exists($this->key_filename,$episode_info)) {
			$this->filename = $episode_info[$this->key_filename];
		}
	}
	
	public function get_title() {
		return $this->title;
	}
	public function get_ep_title() {
		return $this->ep_title;
	}
	public function get_ep_number() {
		return $this->ep_number;
	}
	public function get_synopsis() {
		return $this->synopsis;
	}
	public function get_image_url() {
		return $this->image_url;
	}
	public function get_first_air() {
		return $this->first_air;
	}
	public function get_program_id() {
		return $this->program_id;
	}
}
?>
<?php

class dvr_series {
	private $key_series_id = 'SeriesID';
	private $key_title = 'Title';
	private $key_category = 'Category';
	private $key_image_url = 'ImageURL';
	private $key_start_time = 'StartTime';
	private $key_episodes_url = 'EpisodesURL';
	private $key_update_id = 'UpdateID';

	private $series_id = '';
	private $title = '';
	private $category = '';
	private $image_url = '';
	private $start_time = '';
	private $episodes_url = '';
	private $update_id = '';

	private $episodes_count = '0';
	
	public function dvr_series($series_info){
		error_log("Processing Series " . print_r($series_info, true));
		if (array_key_exists($this->key_series_id,$series_info)) {
			$this->series_id = $series_info[$this->key_series_id];
		}
		if (array_key_exists($this->key_title,$series_info)) {
			$this->title = $series_info[$this->key_title];
		}
		if (array_key_exists($this->key_category,$series_info)) {
			$this->category = $series_info[$this->key_category];
		}
		if (array_key_exists($this->key_image_url,$series_info)) {
			$this->image_url = $series_info[$this->key_image_url];
		}
		if (array_key_exists($this->key_start_time,$series_info)) {
			$this->start_time = $series_info[$this->key_start_time];
		}
		if (array_key_exists($this->key_episodes_url,$series_info)) {
			$this->episodes_url = $series_info[$this->key_episodes_url];
		}
		if (array_key_exists($this->key_update_id,$series_info)) {
			$this->update_id = $series_info[$this->key_update_id];
		}
	}
	
	private function count_episodes($url){
		error_log('Fetching Episode information from ' . $url);
		$json = getCachedJsonFromUrl($url);
		error_log('Got ['. print_r($json,true) . ']');
		$this->episodes_count = count($json);
	}

	public function get_series_id() {
		return $this->series_id;
	}
	public function get_title() {
		return $this->title;
	}
	public function get_category() {
		return $this->category;
	}
	public function get_image_url() {
		return $this->image_url;
	}
	public function get_start_time() {
		return $this->start_time;
	}
	public function get_episodes_url() {
		return $this->episodes_url;
	}
	public function get_update_id() {
		return $this->update_id;
	}
	public function get_episodes_count() {
		return $this->episodes_count;
	}
	public function process_episodes() {
		if ($this->episodes_count == 0 )	{
			$this->count_episodes($this->episodes_url);
		}
	}
}

?>
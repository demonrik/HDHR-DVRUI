<?php

class DVRUI_Engine_LogEntry {
	private $timestamp = '';
	private $type = '';
	private $subtype = '';
	private $filename = '';
	private $info = '';
	
	private $timestamp_pattern = "/\d{8}-\d{2}:\d{2}:\d{2}\s/";
	private $type_pattern = '/\s?[a-zA-Z]*:\s/';
	private $subtype_pattern = '/\s?[a-zA-Z]*: /';
	private $info_pattern = '/\s?[a-zA-Z]*$/';
	
	private function extractTimeStamp($entry) {
		if (preg_match($this->timestamp_pattern, $entry, $matches)) {
			$this->timestamp = trim($matches[0]);
			$remainder = str_replace($matches[0],"",$entry);
			return $remainder;
		}
		return $entry;
	}

	private function extractType($entry) {
		if (preg_match($this->type_pattern, $entry, $matches)) {
			$this->type = trim($matches[0]);
			$remainder = str_replace($matches[0],"",$entry);
			return $remainder;
		}
		return $entry;
	}

	private function extractSubType($entry) {
		if (preg_match($this->subtype_pattern, $entry, $matches)) {
			$this->subtype = trim($matches[0]);
			$remainder = str_replace($matches[0],"",$entry);
			return $remainder;
		}
		return $entry;
	}

	private function extractFilename($entry) {
		return $entry;
	}

	private function extractInfo($entry) {
		$this->info = $entry;
	}

	public function DVRUI_Engine_LogEntry($entry) {
		$remainder = $this->extractTimeStamp($entry);
		$remainder = $this->extractType($remainder);
		if (!strcmp($remainder,'Recorded') 
		   or strcmp($remainder,'Recording')
		   or strcmp($remainder,'System')){
			$remainder = $this->extractSubType($remainder);
		}
		$this->ExtractInfo($remainder);
	}

	public function getLogTimestamp() {
		return $this->timestamp;
	}
	public function getLogType() {
		return $this->type;
	}
	public function getLogSubType() {
		return $this->subtype;
	}
	public function getFilename() {
		return $this->filename;
	}
	public function getLogInfo() {
		return $this->info;
	}
}

?>
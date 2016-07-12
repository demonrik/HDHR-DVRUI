<?php
  
require_once("includes/dvrui_recordengine_logentry.php");
  
class DVRUI_Engine_LogFile {
	
	private $logEntries = array();
	private $position = 0;
	
	public function DVRUI_Engine_LogFile($filename) {
		if (file_exists($filename)) {

			$lines = file($filename);
			// read line and convert to entry
			for ($i=0; $i<count($lines); $i++) {
				$entry = new DVRUI_Engine_LogEntry($lines[$i]);
				$this->logEntries[$i]['Timestamp'] = $entry->getLogTimestamp();
				$this->logEntries[$i]['Type'] = $entry->getLogType();
				$this->logEntries[$i]['SubType'] = $entry->getLogSubType();
				$this->logEntries[$i]['Info'] = $entry->getLogInfo();
			}
		}
	}
	
	public function getNextEntry() {
		$this->position++;
		return $this->logEntries[$this->position];
	}
	
	public function getPrevEntry() {
		$this->position--;
		return $this->logEntries[$this->position];
	}
	
	public function getEntryAt($pos) {
		$this->position = $pos;
		return $this->logEntries[$this->position];
	}
	
	public function getNumEntry() {
		return count($this->logEntries);
	}
	
	public function isTypeError($pos) {
		$this->position = $pos;
		$entry = $this->logEntries[$this->position];
		if ($entry['Type']=='Error:') {
			return TRUE;
		} else {
			return FALSE;
		}
	}

	public function isTypeStatus($pos) {
		$this->position = $pos;
		$entry = $this->logEntries[$this->position];
		if ($entry['Type']=='Status:') {
			return TRUE;
		} else {
			return FALSE;
		}
	}

	public function isTypePlayback($pos) {
		$this->position = $pos;
		$entry = $this->logEntries[$this->position];
		if ($entry['Type']=='Playback:') {
			return TRUE;
		} else {
			return FALSE;
		}
	}

	public function isTypeRecording($pos) {
		$this->position = $pos;
		$entry = $this->logEntries[$this->position];
		if ($entry['Type']=='Recording:') {
			return TRUE;
		} else {
			return FALSE;
		}
	}

	public function isTypeRecorded($pos) {
		$this->position = $pos;
		$entry = $this->logEntries[$this->position];
		if ($entry['Type']=='Recorded:') {
			return TRUE;
		} else {
			return FALSE;
		}
	}

	public function infoContainsError($pos) {
		$this->position = $pos;
		$entry = $this->logEntries[$this->position];
		if (strpos($entry['Info'], 'error') !== false) {
			return TRUE;
		} else {
			return FALSE;
		}
	}

}
?>
<?php

class DVRUI_HDHRcontrols {
	private $hdhr_sh = '';

	public function DVRUI_HDHRcontrols($hdhr) {
		if (file_exists($hdhr)) {
			$this->hdhr_sh = $hdhr;
		}
	}

	private function exec_hdhr_sh($option) {
		if ($this->hdhr_sh != '') {
			$cmd = $this->hdhr_sh . ' ' . $option;
			$output = shell_exec($cmd);
			echo $output;
			$output = 'executed shell with option: ' . $option;

			return $output;
		}
		return NULL;
	}

	public function shutdown_DVR() {
		$output = $this->exec_hdhr_sh('stop');
		//if ($output != NULL) {
			// process output for success/fail;
		//}
		return True;
	}
	
	public function start_DVR() {
		$output = $this->exec_hdhr_sh('start');
		//if ($output != NULL) {
			// process output for success/fail;
		//}\
		return True;
	}

	public function restart_DVR() {
		$output = $this->exec_hdhr_sh('restart');
		//if ($output != NULL) {
			// process output for success/fail;
		//}
		return True;
	}
	
}

?>
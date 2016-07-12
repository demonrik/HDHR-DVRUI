<?php
error_reporting(E_ALL);
require_once("vars.php");

class DVRUI_Engine_Config {
	
    private $configFileName = NULL;
    private $configFileExists = False;
    private $configArray = NULL;
	
	// Default Constructor - nothing provided - do nothing yet...
	public function DVRUI_Engine_Config() {
		if (file_exists(DVRUI_Vars::DVR_qpkgPath . "/" . DVRUI_Vars::DVR_config)) {
			$this->configFileName = DVRUI_Vars::DVR_qpkgPath . "/" .  DVRUI_Vars::DVR_config;
			$this->configFileExists = True;
			$this->configArray = parse_ini_file(DVRUI_Vars::DVR_qpkgPath . "/" . DVRUI_Vars::DVR_config);
		} else {
			$this->configFileName = DVRUI_Vars::DVR_qpkgPath . "/" . DVRUI_Vars::DVR_config . " Does Not Exist";
			$this->configFileExists = False;
		}
	}
	
	public function getConfigFileName() {
		return $this->configFileName;
	}
	
	public function configFileExists() {
		return $this->configFileExists;
	}
	
	public function getRecordPath() {
		$retVal = 'Path not Set';
		if (array_key_exists('RecordPath', $this->configArray)) {
			$retVal = $this->configArray['RecordPath'];
		}
		return $retVal;
	}

	public function setRecordPath($record_path) {
		$this->configArray['RecordPath'] = $record_path;
	}

	public function getServerPort() {
		$retVal = 'Path not Set';
		if (array_key_exists('Port', $this->configArray)) {
			$retVal = $this->configArray['Port'];
		}
		return $retVal;
	}

	public function setServerPort($serverPort) {
		$this->configArray['Port'] = $serverPort;
	}

    public function writeConfigFile() {
    	echo 'Writing Config File';
        $content = "";
        foreach($this->configArray as $key => $val) {
            if (is_array($val)) {
                foreach($val as $skey => $sval) {
                    $content .= $key . '=' . $val . "\n";
                }
            } else {
                $content .= $key . '=' . $val . "\n";
            }
        }
        $handle = fopen($this->configFileName, 'w');
        if (!$handle) {
            return False;
        } else {
            $retVal = fwrite($handle, $content);
            fclose($handle);
            return $retVal;
        }
    }
}
?>
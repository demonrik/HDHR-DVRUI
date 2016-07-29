<?php
	error_reporting(E_ALL & ~(E_DEPRECATED | E_STRICT));
	opcache_reset();
	require_once("includes/dvrui_hdhrjson.php");
	$baseurl = "http://my.hdhomerun.com/api/";

	$api = $_GET['api'];
	$cmd = $_GET['cmd'];

        $hdhr = new DVRUI_HDHRjson();
        $devices =  $hdhr->device_count();
       	$auth = ""; 
	for ($i=0; $i < $devices; $i++) {
                 $auth .= $hdhr->get_device_auth($i);
        }

	if($api == "rules"){
		$url = $baseurl . "recording_rules?DeviceAuth=" . $auth;
		if($cmd == "delete"){
			$ruleid = $_GET['ruleid'];
			$url .= "&Cmd=delete&RecordingRuleID=";
			$url .= $ruleid;
			call_api($url);
		}elseif ($cmd == "create"){
			$seriesid = $_GET['seriesid'];
			$recentonly = $_GET['recentonly'];
			$url .= "&Cmd=add&SeriesID=" . $seriesid;
			$url .= "&RecentOnly=" . $recentonly;
			echo(call_api($url));	
		}
	}


	// poke each record engine
        $engines =  $hdhr->engine_count();
	for ($i=0; $i < $engines; $i++) {
		$hdhr->poke_engine($i);
	}

       function call_api($url){
                $content = "";
                if (in_array('curl', get_loaded_extensions())){
                        $ch = curl_init();
                        curl_setopt($ch, CURLOPT_URL, $url);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                        curl_setopt($ch, CURLOPT_TIMEOUT, 1);
                        $content = curl_exec($ch);
                        curl_close($ch);
                } else {
                        $context = stream_context_create(
                                array('http' => array(
                                        'header'=>'Connection: close\r\n',
                                        'timeout' => 1.0)));
                        $content = file_get_contents($url,false,$context);
                }
                return $content;
        }


?>


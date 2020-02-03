<?php
	require_once("vars.php");
	define('NO_IMAGE','images/no-preview.png');

	function getJsonFromUrl($url) {
		$content = getURL($url);
		return json_decode($content, true);
	}

	function clearCacheFile($url) {
		$cachefilename = sys_get_temp_dir() . "/hdhrui_" . md5($url);
		unlink($cachefilename);

	}
	function clearCache(){
		$mask = sys_get_temp_dir() . "/hdhrui_*";
		array_map('unlink', glob($mask));
	}

	function getCachedJsonFromUrl($url,$maxAgeSeconds) {

		$cachefilename = sys_get_temp_dir() . "/hdhrui_" . md5($url);
		if(file_exists($cachefilename)){
			$current_time = time();
			$file_time = filemtime($cachefilename);
			if ($current_time - $maxAgeSeconds < $file_time){
				$content = file_get_contents($cachefilename);
			}else{
				unlink($cachefilename);
				$content = getURL($url);
				file_put_contents($cachefilename,$content);
			}
		}else{
			$content = getURL($url);
			file_put_contents($cachefilename,$content);
		}
  	if (is_null($content)) {
  		return null;
  	} else {
  		return json_decode($content, true);
  	}
	}

	function getURL($url){
		$ua = DVRUI_Vars::DVRUI_name . '/' . DVRUI_Vars::DVRUI_version;
		if (in_array('curl', get_loaded_extensions())){
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_TIMEOUT, 10);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
			curl_setopt($ch, CURLOPT_USERAGENT, $ua);
			$content = curl_exec($ch);
			curl_close($ch);
		} else { 
			$context = stream_context_create(
				array('http' => array(
					'header'=>'Connection: close\r\n',
					'user_agent'=>$ua,
					'timeout' => 2.0)));
			$content = file_get_contents($url,false,$context);	
		}
		return $content;

	}
	
	function URLExists($url) {
		$exists   = false;
		$ua = DVRUI_Vars::DVRUI_name . '/' . DVRUI_Vars::DVRUI_version;
		if (in_array('curl', get_loaded_extensions())) {
			$ch = curl_init();   
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_TIMEOUT, 10);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
			curl_setopt($ch, CURLOPT_USERAGENT, $ua);
			curl_exec($ch);
			$response = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			if ($response === 200) $exists = true;
				curl_close($ch);
		} elseif (function_exists('get_headers')) {
			$headers = @get_headers($url);
			if ($headers) {
				if (strpos($headers[0], '404') == false) {
					$exists = true;
				}
			}		
		}
		return $exists;
	}

	function postToUrl($url, $vars) {
		$ua = DVRUI_Vars::DVRUI_name . '/' . DVRUI_Vars::DVRUI_version;	
		if (in_array('curl', get_loaded_extensions())){
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_TIMEOUT, 2);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $vars);
			curl_setopt($ch, CURLOPT_USERAGENT, $ua);
			$result = curl_exec($ch);
			curl_close($ch);
		} else { 
			$context = stream_context_create(
				array('http' => array(
					'header'=>'Connection: close\r\n',
					'method' => 'POST',
                                        'user_agent'=>$ua,
					'content' => $vars,
					'timeout' => 2.0)));
			$result = file_get_contents($url,false,$context);	
		}
	}

?>

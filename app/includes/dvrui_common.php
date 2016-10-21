<?php
	function getJsonFromUrl($url) {
		$content = getURL($url);
		return json_decode($content, true);
	}

	function getCachedJsonFromUrl($url,$maxAgeSeconds) {

		$cachefilename = sys_get_temp_dir() . "/cache_" . md5($url);
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
		return json_decode($content, true);
		
	}
	function getURL($url){
		if (in_array('curl', get_loaded_extensions())){
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_TIMEOUT, 10);
			$content = curl_exec($ch);
			curl_close($ch);
		} else { 
			$context = stream_context_create(
				array('http' => array(
					'header'=>'Connection: close\r\n',
					'timeout' => 2.0)));
			$content = file_get_contents($url,false,$context);	
		}
		return $content;

	}
?>

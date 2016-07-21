<?php
	error_reporting(E_ALL & ~(E_DEPRECATED | E_STRICT));
	opcache_reset();
	require_once("hdhr.php");
	
	$cmdURL = base64url_decode($_GET["rec"]);
	$cmd = $_GET["cmd"];
	$rerec = $_GET["rerecord"];
	$deleteURL = $cmdURL . "&cmd=" . $cmd . "&rerec=" . $rerec;
	echo("CommandURL : " . $cmdURL ."<br/>");
	echo("cmd : " . $cmd . "<br/>");
	echo("rerec : " . $rerec . "<br/>");
	echo("delete URL : " . $deleteURL . "<br/>");

	// paranoid - make sure the URL passed is legit
        $hdhr = new DVRUI_HDHRjson();
        $engines =  $hdhr->engine_count();
	for ($i=0; $i < $engines; $i++) {
		$engineURL = $hdhr->get_engine_base_url($i);
		echo("Found one engine at " . $engineURL . "<br/>");
		if(substr_compare($cmdURL,$engineURL,0,strlen($engineURL)) == 0){
			echo("OK.  Engine match.  Safe to delete <br/>");
			delete_rec($deleteURL);
			echo("done.");
		}
	}


	function base64url_decode($data) { 
	  return base64_decode(str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT)); 
	}


       function delete_rec($url){
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


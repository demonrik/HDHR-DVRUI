<?php
/**
	TinyAjax Library
	
	Can be used with and without behaviors. 
	Custom callback code or automatically generated with behaviors.
	Optional source-elements single or as array to automatically retrieve
	data from and pass as parameters to php-function.
	php-callbacks can be functions, methods in extended TA-class or custom class
	IFRAME-fallback.
	
	
	http://www.metz.se/tinyajax/
	
	License: LGPL / http://www.gnu.org/licenses/lgpl.html
	
	@author Mats Karlsson ( mats (at) metz.se )


	@version 0.9.5

	@since 0.9.2 Removed need for define, added function for script-path
	@since 0.9.2 Added option to export classes per export, last parameter of exportFunction
	@since 0.9.3 2006-02-09 - Bug: private private getCommonJavaScript
	@since 0.9.4 2006-02-10 - supplying own parameters to automatic javascript function overrides get-values
	@since 0.9.4 2006-02-10 - sourceId for exportfunction automatically handles value, innerHTML for id and name
	@since 0.9.4 2006-02-16 - automatic formhandling, use TinyAjax::getPostData($formParameter) to extract in php-code
	@since 0.9.4 2006-02-21 - setScriptPath now handles trailing /
	@since 0.9.4 2006-02-21 - graceful degradation to both iframe and browsers with javascript disabled
	@since 0.9.4 2006-02-24 - prefixing exportFunction parameter callback with # sets value or innerhtml.
	@since 0.9.4 2006-03-01 - setting RequestUri moved to process from constructor if not previously set
	@since 0.9.5 2006-03-21 - behaviors and getjavascript now returns html instead of using ob_*
	@since 0.9.5 2006-04-11 - utf8-bug fixed decoding data when client expects utf8-data (new parameter to constructor)
	@since 0.9.5 2006-04-11 - #-bug using get-requests, causing data to be cut off.
	@since 0.9.5 2006-04-12 - returnelement appended when specifying return-element with # calling script using own parameters.
	@since 0.9.5 2006-05-02 - Tries to figure out include-path if not defined using "include", "../include" and current directory
	@since 0.9.5 2006-05-02 - defaults for get/drawJavascript changed to (false, true) ie, don't embed common script and draw script-tag.
	@since 0.9.5 2006-05-08 - form-data &-problem fixed
	@since 0.9.5 2006-05-08 - TinyAjax::getPostData also sets $_POST to form-data
	@since 0.9.5 2006-05-09 - Version-string added: TinyAjax->version()
	@since 0.9.5 2006-05-29 - Bug in arrayDecode()-script, thnx OpEx
	@since 0.9.5 2006-05-29 - returning empty behavior no longer causes error.
	@since 0.9.5 2006-05-29 - automatic setting request-uri fixed for linux, REQUEST_URI if available, otherwise SCRIPT_NAME

*/

	define('TINYAJAX_VERSION', '0.9.5');

	if(!defined('TINYAJAX_PATH')) {
		if(file_exists('include' . '/TinyAjaxBehavior.php')) {
			define('TINYAJAX_PATH', 'include');
		} else if (file_exists('../include' . '/TinyAjaxBehavior.php')) {
			define('TINYAJAX_PATH', '../include');
		} else if (file_exists('TinyAjaxBehavior.php')) {
			define('TINYAJAX_PATH', '.');
		} else {
			die("TINYAJAX_PATH not defined, you need to define where the TinyAjax-files are located");
		}
	}

	require_once(TINYAJAX_PATH . '/TinyAjaxBehavior.php');

	
class TinyAjax
{
	private $mDebug = 0;
	private $mDrawLoading = 0;
	private	$mJavaScriptShowed = 0;
	
	/**
	 * @var array array containing exported functions
	 */
	private $mExports = array();
	private $mRequestType = "GET";
	private $mRequestUri;
	private $mUseIFrame = 0;
	private $mScriptPath = TINYAJAX_PATH;
	private $mUtf8 = false;
	
	/**
	  * @var Template $template where to automatically set javascript if we use templates
	  */
	private $template;
	private $templateHeadField;
	
	
	private	$callbackClass;
	
	
	
	private $mBehaviors = array();

	function __construct($UTF8 = false) {
		
		$this->mUtf8 = $UTF8;
		//MK - XHR == very buggy in IE, use iframe instead?
		//TODO check for ie50 to use XHR
		//$agent = $_SERVER['HTTP_USER_AGENT'];
		//if(strpos($agent, "MSIE")) {
		//	$this->setIFrame();
		//}
			
	}
		
	
	/**
	 * Sets the url to call with the callback request.
	 *
	 * @param string $uri url to open
	 */
	public function setRequestUri($uri) {
		$this->mRequestUri = $uri;
	}
	
	/**
	 * Executes the callback function if page is loaded in callback-mode
	 */
	public function process() {
		
		if (sizeof($this->mBehaviors) == 0) {
			$this->addStdBehaviors();
		}

		// Set Request URI if not set manually
		if (!isset($this->mRequestUri)) {
			
			if(isset($_SERVER['REQUEST_URI'])) {
				$this->mRequestUri = $_SERVER['REQUEST_URI'];
			} else if(isset($_SERVER['SCRIPT_NAME'])) {
				$this->mRequestUri = $_SERVER['SCRIPT_NAME'];
			}
		}		
		$mode = "";
		
		//MK - Check if we have a get-ajax-request
		if (! empty($_GET["rs"])) {
			$mode = "get";
		}
		
		//MK - Check if we have a post-ajax-request
		if (!empty($_POST["rs"])) {
			$mode = "post";
		}
		
		
		//MK - Executed when 
		if (empty($mode)) {
			if (isset($this->template) && isset($this->templateHeadField))
			{
				$html = $this->getJavaScript(false, true);
				
				$this->template->append($this->templateHeadField, $html);
			}
			return;
		}

		
		//MK - Get-request
		if ($mode == "get") {
			$this->setLastModified();
			$func_name = $_GET["rs"];
			if (! empty($_GET["rsargs"])) {
				$args = $_GET["rsargs"];
			} else {
				$args = array();
			}

			//MK - Parse form-handling
			if (! empty($_GET["post"])) {
				$args = $_GET["post"];
			}
		}
		else {
			//MK - Post request
			$func_name = $_POST["rs"];
			if (! empty($_POST["rsargs"])) {
				$args = $_POST["rsargs"];
			} else {
				$args = array();
			}
		}
		
		$callable = false;
		$customClass = null;
		foreach ($this->mExports as $val) {
			if (!is_array($val))
			{
				if ($func_name == $val)
					$callable = true;
			}
			else 
			{
				if ($func_name == $val[0]) {
					$customClass = $val[3];
					$callable = true;
				}
			}
		}
		
		
		//MK - urldecode $args here ...
		$asize = sizeof($args);
		for ($x = 0; $x < $asize; $x++) {
			$args[$x] = $this->decode($args[$x]);
		}

		
		
		if (! $callable) {
			//MK - Export not found
			echo "-:$func_name not callable";
		} else {
			//MK - Export found, now let's figure out how it's exported and call it
			echo "+:";
			if (isset($this->callbackClass) && is_callable(array($this->callbackClass, $func_name))) {
				
				//MK - We have a global callback class and function is callable
				$result = call_user_func_array(array($this->callbackClass, $func_name), $args);
				
			} elseif ($customClass != null && is_callable(array($customClass, $func_name))) {

				//MK - We have a custom class for this function
				$result = call_user_func_array(array($customClass, $func_name), $args);
				
			}else if (is_callable(array($this, $func_name), false)) {
				
				//MK - Extended TinyAjax, so function is in derived class
				$result = call_user_func_array(array($this, $func_name), $args);			
				
			} else {
				
				//MK - Regular function 
				$result = call_user_func_array($func_name, $args);
				
			}
			echo $result;
		}
		exit;
	}
	
	
	
	/**
	 * Automatically adds required javascript to template's head tag if
	 * template is specified
	 *
	 * @param ITemplate $template instance of page-template
	 * @param string $headField name of head-field
	 */
	public function setTemplate(ITemplate $template, $headField)
	{
		$this->template = $template;
		$this->templateHeadField = $headField;
	}
	
	/**
	 * Returns a stub for function specified in $func_name
	 * Depending how function was exported different stubs are generated.	
	 *
	 * @param string $func_name Name of function to create stub for 
	 *
	 * @return string the entire stub
	 */
	private function getStub($func_name) 
	{
		if (!is_array($func_name)) {
			$htmlHead = "";
			$html = "
			
			//Stub for $func_name
			function $func_name() {
				aj_call(\"$func_name\", $func_name.arguments);
			}
			\n";
		} else {
			$params = "";
			$html = "";
			$htmlHead = "
			
			//Stub for {$func_name[0]}
			function {$func_name[0]}() {\n";
			
			
			if (is_array($func_name[1])) {
				//MK - array of parameters to pass to function
				
				for ($x = 1; $x <= sizeof($func_name[1]); $x++) {
					$html .= "\t\t\t\tvar aj_tmp$x = getValue(\"" . $func_name[1][$x-1] . "\");\n";
					$params .= "aj_tmp" . $x ;
					if($x != sizeof($func_name[1])) {
						$params .= ", ";
					}
				}
			} else if (!is_null($func_name[1])) {
				//MK - just one parameter
				$html .= "\t\t\t\tvar aj_tmp = getValue(\"" . $func_name[1] . "\");\n";
				
				$params .= "aj_tmp";
			} 
			
			
			//MK - Custom callback
			if (!is_null($func_name[2])) {

				//MK - Callback _not_ prefixed with # (means custom callback)
				if (substr($func_name[2], 0, 1) != "#") {

					//MK - set callback-parameter to true
					$htmlHead .= "	if({$func_name[0]}.arguments.length > 0) {
						return aj_call(\"{$func_name[0]}\", {$func_name[0]}.arguments, true);
					}
					\n";
					
					//MK - No SourceID then use func.arguments
					if(is_null(($func_name[1]))) {
						$html .= "
						var y = {$func_name[0]}.arguments.length;
						var arr = new Array(y+1);
						for(var x = 0; x < y; x++) {
							arr[x] =  {$func_name[0]}.arguments[x];
						}
						arr[y] = {$func_name[2]};
						return aj_call(\"{$func_name[0]}\", arr, true );\n\t\t\t}\n\n";
					} else {
						
						$html .= "\n\t\t\t\treturn aj_call(\"{$func_name[0]}\", new Array( " . $params . ", " . $func_name[2]."), true );\n\t\t\t}\n\n";
					}
				} else {
					//MK - Prefixed with # then we should set the value (set last parameter to false)
					//MK - remove the # when calling aj_call

					//MK - set callback-parameter to false
					$htmlHead .= "	if({$func_name[0]}.arguments.length > 0) {
						return aj_call(\"{$func_name[0]}\", appendArr({$func_name[0]}.arguments, '" . substr($func_name[2],1) . "'), false);
					}
					\n";

					//MK - No SourceID then use func.arguments
					if (is_null(($func_name[1]))) {
						$html .= "
						var y = {$func_name[0]}.arguments.length;
						var arr = new Array(y+1);
						for(var x = 0; x < y; x++) {
							arr[x] =  {$func_name[0]}.arguments[x];
						}
						arr[y] = " . substr($func_name[2], 1) .";
						return aj_call(\"{$func_name[0]}\", arr, false );\n\t\t\t}\n\n";
					} else {
						
						$html .= "\n\t\t\t\treturn aj_call(\"{$func_name[0]}\", new Array( " . $params . ", '" . substr($func_name[2], 1) ."'), false );\n\t\t\t}\n\n";
					}
				
				
				}
				
			} else {
				//MK - no callback-parameter
				//MK - 2006-04-06 removed , false
				$htmlHead .= "	if({$func_name[0]}.arguments.length > 0) {
					return aj_call(\"{$func_name[0]}\", {$func_name[0]}.arguments);
				}\n\n";

				if(!is_null($func_name[1])) {
					//MK - 2006-04-06 removed , false
					$htmlHead .= "	if({$func_name[0]}.arguments.length > 0) {
						return aj_call(\"{$func_name[0]}\", {$func_name[0]}.arguments, false);
					}\n\n";
					
					$html .= "\n\t\t\t\treturn aj_call(\"{$func_name[0]}\", new Array( " . $params . ") );\n\t\t\t}\n\n";
				} else {
					//MK - 2006-04-06 removed , false
					$htmlHead .= "	if({$func_name[0]}.arguments.length > 0) {
						return aj_call(\"{$func_name[0]}\", {$func_name[0]}.arguments);
					}\n\n";

					$html .= "\n\t\t\t\treturn aj_call(\"{$func_name[0]}\", {$func_name[0]}.arguments );\n\t\t\t}\n\n";
				}
			}
			
		}
		return $htmlHead . $html;
	}
	
	/**
	 * Exports a function to call in php
	 * calling the javascript function creates asynchronous call to php function.
	 * If sourceId is not specified then you have to supply parameters to the function
	 * otherwise stubs are generated to automatically retrieve values from source-elements
	 * If callback is specified it will call that function otherwise behaviors specifies
	 * what happens client-side.
	 * 
	 * If callback-parameter is prefixed with a # then that element gets the returned value, 
	 * sets value if element support it, otherwise it sets innerhtml.
	 * 
	 *
	 * @param string $functionName Function to export
	 * @param array/string $aSourceId, array or string containing of id to get value from, if set to null then you have to supply callback parameters yourself
	 * @param string $callback if set then custom callback javascript-function, no behaviors used
	 * @param obj $callback if set then the function is called in this class
	 * 
	 * @since 0.9.2 - Optional custom callback-class for function to call
	 */
	function exportFunction($functionName, $sourceId = null, $callback = null, $class = null)
	{
		if($sourceId != null || $callback != null || $class != null) {
			$this->mExports[] = array($functionName, $sourceId, $callback, $class);
		} else {
			$this->mExports[] = $functionName;
		}
	}
	
	
	/**
	 * Adds a TinyAjax-behavior to be supported on clientside.
	 * Generates javascript for callback to behavior and javascript for behavior
	 *
	 * @param Tab $behavior behavior to add
	 */
	function addBehavior(Tab $behavior) 
	{
		$this->mBehaviors[] = $behavior;
	}
	
	
	/**
	 * Adds default-behaviors from for example TabSetValue, TabInnerHtml etc.
	 * If no behaviors are added then process automatically adds standard behaviors.
	 * If you created your own behavior and added it, then you must add standard 
	 * behaviors if you want to use them.
	 * @since 2006-03-16 Added TabRedirect as standard behavior
	 */
	public function addStdBehaviors()
	{
		//MK - These are the behaviors we support for php-function to return
		$this->addBehavior( new TabAlert());
		$this->addBehavior( new TabSetValue());
		$this->addBehavior( new TabInnerHtml());
		$this->addBehavior( new TabInnerHtmlAppend());
		$this->addBehavior( new TabInnerHtmlPrepend());
		$this->addBehavior( new TabAddOption());
		$this->addBehavior( new TabClearOptions());
		$this->addBehavior( new TabRemoveSelectedOption());
		$this->addBehavior( new TabRedirect());
	}
		
	/**
	 * Draws common javascript and the autogenerated stubs
	 * @var bool $getCommonJavascript If we should include common Ajax-code from TinyAjax.js
	 * @var bool $includeScriptTag if we should draw the script /script-tags
	 */
	public function drawJavaScript($getCommonJavascript = false, $includeScriptTag = true) {
		return $this->getJavaScript($getCommonJavascript, $includeScriptTag);
	}	
	
	/**
	 * Retrieves common javascript and the exports without script-tag
	 *
	 * @var bool $getCommonJavascript If we should include common Ajax-code in this page
	 * @var bool $includeScriptTag If we should include the script /script-tags
	 * @return string javascript as string without script-tags
	 */
	public function getJavaScript($getCommonJavascript = false, $includeScriptTag = true) {
		$html = "";
		
		//MK - Don't include common-javascript, refer to it AND include script-tags
		if(!$getCommonJavascript &&  $includeScriptTag) {
			$html .= "<script type=\"text/javascript\" src=\"".$this->mScriptPath."/TinyAjax.js\"></script>\n";
		}
		
		//MK - Include script-tags
		if($includeScriptTag) {
			$html .= "<script type=\"text/javascript\">\n";
		}
		
		
		
		//MK - Extract common JavaScript
			$html .= $this->getCommonJavaScript($getCommonJavascript);
			$this->mJavaScriptShowed = 1;
		
		//MK - Generate the stubs
		foreach ($this->mExports as $func) {
			$html .= $this->getStub($func);
		}

		//MK - Extract behaviors
		foreach ($this->mBehaviors as $func) {
			$html .= $func->getJavaScript();
		}
		
		$html .= $this->getFuncStub();
		
		
		//MK - Include script-tags
		if($includeScriptTag) {
			$html .= "</script>\n";
		}
		
		return $html;
	}
	
	/**
	 * Returns the common Ajax-javascript xmlhttp-creation etc.
	 *
	 * @var $getCommonJavascript if TinyAjax.js should be included in this page
	 * @return string javascript to print
	 */
	private function getCommonJavaScript($getCommonJavascript = true) {
		
		
		$html = "
		var xml_request_type = \"" .  $this->mRequestType . "\";
		var use_iframe = " .  $this->mUseIFrame . ";
		var show_loading = " .  $this->mDrawLoading . ";
		var request_uri = \"" . $this->mRequestUri . "\";" ;

		if($getCommonJavascript){
			$html .= file_get_contents($this->mScriptPath . "/TinyAjax.js");
		}
		return $html;
	}
	
	
	/**
	 * Private helper function that generates the aj_process2-script
	 *
	 * @return string containing script
	 */
	private function getFuncStub(){

		$html = "\n\nfunction aj_process2(data)\n{\n\tvar fnc = data[0];\n\n";

		foreach($this->mBehaviors as $func){
			$html .= "\n\t if(fnc == '" . $func->getFunctionName() . "'){ ";
			$html .= "" . $func->getFunctionName() . "(data); return; }";
		}
		
		//MK - 2006-05-29
		$html .="\n\n\t if(data[0].length == 0) { return; }";
		
		$html .= "\n\n\t alert('Missing behavior or php-error:' + data[0]);";
		$html .= "\n\n}";	

		return $html;
		
		
	}
	
	
	/**
	 *	Sets last modified and no-cache options so browser doesn't cache it
	 */
	public function setLastModified() {
		header ("Expires: Mon, 26 Jul 1997 05:00:00 GMT");    // Date in the past
		header ("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
		// always modified
		header ("Cache-Control: no-cache, must-revalidate");  // HTTP/1.1
		header ("Pragma: no-cache");                          // HTTP/1.0
	}
		
	
	/**
	 * Sets the request type to post or get, default is "get" if not set 
	 * with this function
	 *
	 * @param string $type type of callback request, post or get
	 */
	public function setRequestType($type)
	{
		$tmp = strtoupper($type);
		if ($tmp == "GET" || $tmp == "POST") {
			$this->mRequestType = $tmp;
		}

		//MK - If we have a post and use iframe then remove iframe...
		if($this->mRequestType == "POST" && $this->mUseIFrame) {
			$this->mUseIFrame = 0;
		}
	}
	
	/**
	 * Forces iframe-fallback, only works on IE and get-requests
	 */
	public function setIFrame() {
		$this->mUseIFrame = 1;
	}
	
	/**
	 * Shows a gmail-style "Loading..."-notification in the upper
	 * right corner.
	 * Must be set before process()
	 *
	 * @param bool $bShow if loading notification should be shown
	 */
	public function showLoading($bShow = true)
	{
		if($bShow)
			$this->mDrawLoading = 1;
		else
			$this->mDrawLoading = 0;
	}
	
	/**
	 * Conveniencefunction that encodes a 2dimensional array for passing back to javascript
	 * @deprecated 
	 * @param array $data 2dimensional array to encode
	 * @return string containing all data in array
	 */
	public function encodeArray($data)
	{
		$retVal = "";
		foreach($data as $value)
		{
			if($retVal != "")
				$retVal .= "~";
				
			$tmp = "";
			foreach ($value as $col) {
				if($tmp != "")
					$tmp .= "|";
				$tmp .= urlencode($col);
				
			}
			$retVal .= $tmp;
			
		}
		return $retVal;
	}
	
	
	
	/**
	 * Sets the global callback-class, if this is set then TA first attempts to locate
	 * export in that class, otherwise it calls the global function 
	 *
	 * @param object $classInstance reference to instanciated class to call exported methods in.
	 */
	public function setCallbackClass($classInstance) {
		$this->callbackClass = $classInstance;
	}
	
	
	/**
	 * Path to common JavaScript-file (TinyAjax.js)
	 *
	 * @param string $script_path path to common script.
	 */
	public function setScriptPath($script_path) {
		
		if(substr(strrev($script_path), 0, 1) == "/") {
			$script_path = substr($script_path, 0, strlen($script_path) - 1);;
		}
		
		$this->mScriptPath = $script_path;
	}

	
	/**
	 * Static function that converts a form-parameter in callback class to
	 * a key->val array containing all form data (like $_POST)
	 *
	 * @param unknown_type $data parameter containing form-data
	 * @return array key->val array containing all form data
	 */
	public static function getPostData($data) {
		$row = split("!ROW!", $data);
		$arr = "";
		foreach($row as $val) {
			$tmp = split("!COL!", $val);
			if(sizeof($tmp) > 1)
				$arr[$tmp[0]] = $tmp[1];
			else
				$arr[$tmp[0]] = "";
		}
		$_POST = $arr;
		return $arr;
	}
	
	
	/**
	 * Helper-function that decodes the arguments before passing
	 * them to the callback function (in process)
	 *
	 * @param unknown_type $data
	 * @return unknown
	 */
	private function decode($data) {
		
		$data = str_replace("!!tilde!!", "~", $data);
		$data = str_replace("!!pipe!!", "|", $data);
		$data = str_replace("!!plus!!", "+", $data);
		$data = str_replace("!!backslash!!", "\\", $data);
		$data = str_replace("!!excl!!", "!", $data);
		$data = str_replace("!!hash!!", "#", $data);
		
		
		if($this->mUtf8) {
			//MK - We wan't data in utf8, don't decode
			return $data;
		} else {
			//MK - Don't want data in utf8, decode it
		return utf8_decode($data);
		}
	}
	
	
	/**
	 * Returns the version-string this library
	 *
	 * @return string current version
	 */
	public function version() {
		return 'TinyAjax v' . TINYAJAX_VERSION;
	}
}

?>
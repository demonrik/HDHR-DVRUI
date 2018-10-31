<?php


/**
 * Response-class to be used from serverside-function, add static behaviors and
 * data to set/change and return the behavior with getString()
 * @since 0.9.2 new static function isCallback
 */
class TinyAjaxBehavior
{
	private $mUtf8 = false;
	
	function __construct($UTF8 = false) {
		$this->mUtf8 = $UTF8;
	}

	private $behavior = "";

	/**
	 * Adds behavior to return to callback javascript
	 * 
	 * @param Tab-behavior $behavior as string from Tab::getBehavior()
	 */
	function add($behavior){
		if($this->behavior != "") {
			$this->behavior .= "~";
		}

		$first = true;
		foreach($behavior as $val) {

			if(!$first) {
				$this->behavior .= "|";
			} else {
				$first = false;
			}
			
			$val = str_replace("|", "!!pipe!!", $val);
			$val = str_replace("~", "!!tilde!!", $val);
			if(!$this->mUtf8) {
				$val = utf8_encode($val);
			}
			
			$this->behavior .= $val;
			
		}
	}

	
	/**
	 * Checks if it's a AJAX callback, this allows callback functions
	 * to be used both with TinyAjax and regular serverside code.
	 * If it's a callback return behavior, otherwise return result
	 *
	 * @return boolean true if it's a AJAX callback, false if not
	 */
	public static final function isCallback() {
		return (isset($_GET['rs']) || isset($_POST['rs']));
	}
	
	/**
	 * Returns all behaviors to execute as a string to javascript callback
	 *
	 * @return string Behaviors
	 */
	function getString() { return $this->behavior; }
}


/**
 *	Abstract TinyAjaxBehavior-class which defines behaviors
 *  Subclasses need to implement getScript and static getBehavior
 * @since 2006-01-18 changed $mDrawn to boolean from array, array not needed
 * @since 2006-01-18 getScript protected and static getBehavior implemented (can't be abstract static :-( )
 */
abstract class Tab{

	private $mDrawn = false;

	abstract protected function getScript();

	public static function getBehavior() { 
		return ""; 
	}
	
	public final function getFunctionName(){
		return get_class($this);
	}
	

	public function getJavaScript()
	{
		return $this->getScript();
	}

	
}



class TabAlert extends Tab
{
	protected function getScript(){
		return "function " . get_class() . "(data){
			alert(data[1]);
		}
		";
	}

	public static function getBehavior($data) {
		return array(get_class(), $data);
	}
}

class TabSetValue extends Tab
{
	protected function getScript(){

		$html = "function " . get_class($this) . "(data){";
		$html .= " document.getElementById(data[1]).value = decodeSpecialChars(data[2]);\n}\n";

		return $html;
	}

	public static function getBehavior($form_id, $data) {
		return array(get_class(), $form_id, $data);
	}
}

class TabInnerHtml extends Tab
{
	protected function getScript(){

		return "function " . get_class($this) . "(data){
	document.getElementById(data[1]).innerHTML = decodeSpecialChars(data[2]);\n}\n";
	}

	public static function getBehavior($form_id, $data) {
		return array(get_class() , $form_id,  $data);
	}
}

class TabInnerHtmlPrepend extends Tab
{
	protected function getScript(){

		return "function " . get_class($this) . "(data){
	document.getElementById(data[1]).innerHTML = decodeSpecialChars(data[2]) + document.getElementById(data[1]).innerHTML;\n}\n";
	}

	public static function getBehavior($form_id, $data) {
		return array(get_class(), $form_id, $data);
	}
}
class TabInnerHtmlAppend extends Tab
{
	protected function getScript(){

		return "function " . get_class($this) . "(data){
	document.getElementById(data[1]).innerHTML = document.getElementById(data[1]).innerHTML + decodeSpecialChars(data[2]);\n}\n";
	}

	public static function getBehavior($form_id, $data) {
		return array(get_class(), $form_id, $data);
	}
}

class TabAddOption extends Tab
{
	protected function getScript(){

		return "function " . get_class($this) . "(data){
	var sel = document.getElementById(data[1]);
	sel.options[sel.options.length] = new Option(decodeSpecialChars(data[3]), decodeSpecialChars(data[2]), true, false);
	if(data[4] != 0)
		sel.selectedIndex = sel.options.length-1;\n}\n";
	}

	public static function getBehavior($element_id, $id, $value, $select_it = 0) {
		$select_it ? 1 : 0;
		return array(get_class(), $element_id, $id, $value, $select_it);
	}

}

class TabClearOptions extends Tab
{
	protected function getScript(){

		return "function " . get_class($this) . "(data){
	var sel = document.getElementById(data[1]);
	sel.options.length = 0;\n}\n";
	}

	public static function getBehavior($element_id) {
		return array(get_class(), $element_id);
	}

}


class TabRemoveSelectedOption extends Tab
{
	protected function getScript(){

		return "function " . get_class($this) . "(data){
	var sel = document.getElementById(data[1]);
	sel.options[sel.options.selectedIndex] = null;
		
	}\n";
	}

	public static function getBehavior($element_id) {
		return array(get_class(), $element_id);
	}

}



class TabSetWindowFocus extends Tab
{
	protected function getScript(){
		return "function " . get_class($this) . "(data){\n\twindow.focus();\n}\n";
	}

	public static function getBehavior() {
		return array(get_class());
	}

}

class TabSetBackgroundColor extends Tab 
{
	protected function getScript(){

		return "function " . get_class($this) . "(data){
	var o = document.getElementById(data[1]);
	if(o){
		var col = data[2];
		o.style.backgroundColor = col;
			}\n}\n";
	}

	public static function getBehavior($element_id, $color) {
		return array(get_class(), $element_id, $color);
	}
	
}

class TabRedirect extends Tab 
{
	protected function getScript(){
		return "function " . get_class($this) . "(data){ " 
				. "\n\t document.location = data[1]; \n\t }\n";
	}
		
	public static function getBehavior($location) {
		return array(get_class(), $location);
	}
	
}


class TabEval extends Tab 
{
	protected function getScript(){
		return "function " . get_class($this) . "(data){ 
		 //alert(data[1]);
		 eval(data[1]); \n\t }\n";
	}
		
	public static function getBehavior($scriptName) {
		return array(get_class(), $scriptName);
	}
	
}



?>
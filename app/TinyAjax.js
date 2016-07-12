
var form_data = '';

function urlDecode( encoded )
{
	var HEXCHARS = "0123456789ABCDEFabcdef";
	var plaintext = "";
	var i = 0;
	while (i < encoded.length) {
		var ch = encoded.charAt(i);
		if (ch == "+") {
			plaintext += " ";
			i++;
		} else if (ch == "%") {
			if (i < (encoded.length-2) && HEXCHARS.indexOf(encoded.charAt(i+1)) != -1 && HEXCHARS.indexOf(encoded.charAt(i+2)) != -1 ) {
				plaintext += unescape( encoded.substr(i,3) );
				i += 3;
			} else {
				//ignore errors, normal %-tag
				plaintext += "%";
				i++;
			}
		} else {
			plaintext += ch;
			i++;
		}
	}
	return plaintext;
}


function arrayDecode( encoded )
{
	var row = encoded.split("~");
	var numRows = row.length ;
	var arr = new Array(numRows);

	for(var x = 0; x < numRows; x++){
		var tmp = row[x].split("|");
		
		//MK - FIX ###plus###
		for(var y = 0; y < tmp.length; y++){
			tmp[y] = decodeSpecialChars(tmp[y]);
		}
		arr[x] = tmp;
	}

	return arr;
}

function decodeSpecialChars(data)
{
	s = new String(data);
	s = s.replace(/\!\!plus\!\!/g,"+");
	s = s.replace(/\!\!backslash\!\!/g,"\\");
	s = s.replace(/\!\!pipe\!\!/g,"|");
	s = s.replace(/\!\!tilde\!\!/g,"~");
	s = s.replace(/\!\!excl\!\!/g,"!");
	s = s.replace(/\!\!hash\!\!/g,"#");
	s = s.replace(/\!\!amp\!\!/g,"&");

	return s;
}

function encodeSpecialChars(data)
{
	s = new String(data);
	s = s.replace(/\!/g,"!!excl!!") ;
	s = s.replace(/\+/g,"!!plus!!") ;
	s = s.replace(/\\/g,"!!backslash!!") ;
	s = s.replace(/\|/g,"!!pipe!!") ;
	s = s.replace(/\~/g,"!!tilde!!") ;
	s = s.replace(/\#/g,"!!hash!!") ;
	s = s.replace(/\&/g,"!!amp!!") ;
	return s;
}	

var numLoading = 0;

function loading_show()
{

	var loading = document.getElementById('loading');
	if (!loading)
	{
		loading = document.createElement('div');
		loading.id = 'loading';
		loading.innerHTML = '<font style="font-family:verdana; font-size:12px; color:white;">Loading...</' + 'font>';
		loading.style.position = 'absolute';
		loading.style.top = '4px';
		loading.style.right = '4px';
		loading.style.backgroundColor = 'red';
		loading.style.width = '65px';
		loading.style.padding = '2px';
		document.getElementsByTagName('body').item(0).appendChild(loading);
	}
	loading.style.display = 'block';
	numLoading++;
}

function loading_hide()
{
	numLoading--;
	if(numLoading < 1) {
		var loading = document.getElementById('loading');
		if (loading) {
			loading.style.display = 'none';
		}
	}
}


var xhrPool = new Array;

function aj_init_object() {

	
	var xmlhttp= false;
	if(xhrPool.length > 0) {
		 xmlhttp = xhrPool.shift();
		 return xmlhttp;
	}
	
	if(xmlhttp) {
		return xmlhttp;
	}
	
	if(use_iframe) {
		xmlhttp = new XMLHttpRequestI();
		return xmlhttp;
	}
	
	/*@cc_on @*/
	/*@if (@_jscript_version >= 5)
	// JScript gives us Conditional compilation, we can cope with old IE versions.
	// and security blocked creation of the objects.
	try {
	xmlhttp = new ActiveXObject("Msxml2.XMLHTTP");
	} catch (e) {
	try {
	xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
	} catch (E) {
	xmlhttp = false;
	}
	}
	@end @*/
	if (!xmlhttp && typeof XMLHttpRequest !== undefined) {
		xmlhttp = new XMLHttpRequest();
	} else if(!xmlhttp) {
		//IFrame fallback for IE
		xmlhttp = new XMLHttpRequestI();
	}
	
	return xmlhttp;
}


function aj_process(data)
{
	for(var x = 0; x < data.length; x++) {
		aj_process2(data[x]);
	}
}



function aj_call(func_name, args, custom_cb) {
	var i;
	var x;
	var uri;
	var post_data;

	uri = request_uri;

	if (xml_request_type == "GET") {
		if (uri.indexOf("?") == -1) {
			uri = uri + "?rs=" + escape(func_name);
		} else {
			uri = uri + "&rs=" + escape(func_name);
		}
		for (i = 0; i < args.length; i++) {
			if(args[i] == 'post_data') {
				uri += form_data;
				form_data = '';
			} else {
				//MK - TODO: Check if args[i] is a array?!
				//uri = uri + "&rsargs[]=" + args[i];
				uri = uri + "&rsargs[]=" + escape(args[i]);
			}
		}
		
		uri = uri + "&rsrnd=" + new Date().getTime();
		post_data = null;
	} else {
		post_data = "rs=" + escape(func_name);
		for (i = 0; i < args.length; i++) {
			if(args[i] == 'post_data') {
				post_data += form_data;
				form_data = '';
			}
			post_data = post_data + "&rsargs[]=" + args[i];
		}
	}

	x = aj_init_object();
	if(!x) { return true; }

	if(show_loading) { loading_show(); }

	x.open(xml_request_type, uri, true);
	if (xml_request_type == "POST") {
		x.setRequestHeader("Method", "POST " + uri + " HTTP/1.1");
		x.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
	}
	x.onreadystatechange = function() {
		
		try {
			if (x.readyState != 4) {
				return;
			}
	
			loading_hide();
	
			if(x.status != 200)
			{
				alert('Error invalid status: ' + x.responseText + ' status: ' + x.status);
				delete x;
				return;
			}
		} catch ( e ) {
			return;
		}

		var status = x.responseText.charAt(0);
		var data = x.responseText.substring(2);

		if (status == "-")
		{
			alert("Callback error: " + data);
			delete x;
			return;
		}

		if (custom_cb === undefined ) {
			aj_process(arrayDecode(urlDecode(data)));
		} else if(custom_cb) {
			args[args.length-1]( "" + data);
		} else {
			setValue(args[args.length-1], data);
		}
		xhrPool.push(x);
	};
	
	x.send(post_data);
	//delete x;
	return false;
}

/*
coded by Kae - http://verens.com/
use this code as you wish, but retain this notice

MK - notice retained, but renamed function to XMLHttpRequestI and
modified initial timeout
*/
XMLHttpRequestI = function() {
	var i=0;
	var url='';
	var responseText='';
	this.onreadystatechange=function(){
		return false;
	};
	
	this.open=function(method,url){
		//TODO: POST methods
		this.i=++kXHR_instances; // id number of this request
		this.url=url;
		var iframe = document.createElement('iframe');
		iframe.id= 'kXHR_iframe_'+this.i+'';
		iframe.type = "text/plain";
		iframe.style.display = 'none';
		//alert(iframe.id);
		document.body.appendChild(iframe);
	};
	
	this.send=function(postdata){
		//TODO: use the postdata
		var el=document.getElementById('kXHR_iframe_'+this.i);
		el.src=this.url;
		kXHR_objs[this.i]=this;
		setTimeout('XMLHttpRequestI_checkState('+this.i+')',200);
	};
	
	return true;
};


function XMLHttpRequestI_checkState(inst){
	var el=document.getElementById('kXHR_iframe_'+inst);
	if(el.readyState=='complete'){
		var responseText=window.frames['kXHR_iframe_'+inst].document.body.childNodes[0].data;
		kXHR_objs[inst].responseText=responseText;
		kXHR_objs[inst].readyState=4;
		kXHR_objs[inst].status=200;
		kXHR_objs[inst].onreadystatechange();
		el.parentNode.removeChild(el);
	}else{
		setTimeout('XMLHttpRequestI_checkState('+inst+')',500);
	}
}
var kXHR_instances=0;
var kXHR_objs=[];


function getValue(element) {
	
	var itm = document.getElementById(element);
	var value = "";
	var x;
	
	if(itm === null) {
		itm = document.getElementsByName(element);
		if(itm !== null) {
			itm = itm[0];
		}
	}
	

	if(itm !== null) {
		
		if(itm.value !== undefined) {
			value = encodeSpecialChars(itm.value);
		} else {
			value = encodeSpecialChars(itm.innerHTML);
		}
	}
	
	if(itm === null) {
		return '';
	}
	
	
	if(itm.type !== undefined) {
	
		if(itm.type == 'select-one') {
			value = encodeSpecialChars(encodeSpecialChars(itm[itm.selectedIndex].value));
		} else if(itm.type == 'select-multiple') {
			value = '';
			for (x = 0; x < itm.length; x++) {
				if(itm.options[x].selected) {
					value += encodeSpecialChars(itm.options[x].value) + ',';
				}
			}
			if(value.length > 0) {
				value = value.substr(0, value.length - 1);
			}
		} else if(itm.type == 'checkbox') {
			if(itm.checked) {
				value = encodeSpecialChars(itm.value);
			} else {
				value = '';
			}
		} else if(itm.type == 'radio') {
			if(itm.checked) {
				value = encodeSpecialChars(itm.value);
			} else {
				value = '';
			}
		}
	}
	
	
	if(itm.elements !== undefined) {
		var col = '!COL!';
		var row = '!ROW!';
		var name;
		var first = true;
		
		value = 'post_data';
		form_data = '&rsargs[]=';
		
		for(x = 0; x < itm.elements.length; x++) {
			if(!first) {
				form_data += row;
			}
			first = false;
			
			var y = itm.elements[x];
			name = '';
			if(y.getAttribute('id') !== null && y.id !== '') {
				name = y.id;
			}
			if(y.getAttribute('name') !== null && y.name !== '') {
				name = y.name;
			}

			if(y.type == 'select-one') {
				form_data +=  name + col + encodeSpecialChars(y[y.selectedIndex].value);
			} else if(y.type == 'select-multiple') {
				var sel = false;
				form_data += name + col;
				for (var z = 0; z < y.length; z++) {
					if(y.options[z].selected) {
						form_data += encodeSpecialChars(y.options[z].value) + ',';
						sel = true;
					}
				}
				if(sel) {
					form_data = form_data.substr(0, form_data.length - 1);
				}
			} else if(y.type == 'checkbox') {
				if(y.checked) {
					form_data += name + col + encodeSpecialChars(y.value);
				} else {
					first = true;
				}
			} else if(y.type == 'radio') {
				if(y.checked) {
					form_data += name + col + encodeSpecialChars(y.value);
				} else {
					first = true;
				}
			} else {
				form_data += name + col + encodeSpecialChars(y.value);
			}
		}
	}
	
	return value;
}

function setValue(element, data) {
	
	var itm = document.getElementById(element);
	
	if(itm === null) {
		itm = document.getElementsByName(element);
		if(itm !== null) {
			itm = itm[0];
		}
	}

	if(itm !== null) {
		if(itm.value != undefined) {
			itm.value = data;
		} else {
			itm.innerHTML = data;
		}
	}
}

function appendArr(args, obj) {
	var arr = new Array;
	for (i = 0; i < args.length; i++) {
			arr.push(args[i]);
	}
	arr.push(obj);
	return arr;
}

"use strict";

var AJAX_REQUESTS=[];
var AJAX_METHODS=[];
var AJAX_TIMEOUTS=[];

var block_data_cache={};

function call_block(url,new_params,target_div,append)
{
	if (typeof block_data_cache[url]=='undefined') block_data_cache[url]=getInnerHTML(target_div); // Cache start position. For this to be useful we must be smart enough to pass blank new_params if returning to fresh state

	var ajax_url=url+'&block_map_sup='+window.encodeURIComponent(new_params);
	if (typeof block_data_cache[ajax_url]!='undefined')
	{
		show_block_html(block_data_cache[ajax_url],target_div,append);
		return;
	}

	// Show loading image

	if (!append)
	{
		target_div.orig_position=target_div.style.position;
		target_div.style.position='relative';
		var loading_image=document.createElement('img');
		loading_image.src='{$IMG;,bottom/loading}';
		loading_image.style.position='absolute';
		loading_image.style.left=(findWidth(target_div)/2-10)+'px';
		loading_image.style.top=(findHeight(target_div)/2-20)+'px';
		target_div.appendChild(loading_image);
	}

	// Make AJAX call
	var func=function(raw_ajax_result) { _call_block(raw_ajax_result,ajax_url,target_div,append) };
	func.accept_raw_response=true;
	load_XML_doc(ajax_url,func);
	
	return false;
}

function _call_block(raw_ajax_result,ajax_url,target_div,append)
{
	target_div.style.position=target_div.orig_position;
	var new_html=raw_ajax_result.responseText;
	block_data_cache[ajax_url]=new_html;
	show_block_html(new_html,target_div,append);
}

function show_block_html(new_html,target_div,append)
{
	setInnerHTML(target_div,new_html,append);
}

/* Calls up a URL to check something, giving any 'feedback' as an error (or if just 'false' then returning false with no message) */
function do_ajax_field_test(url,post)
{
	if (!ajax_supported()) return true;

	if (typeof window.keep_stub!='undefined') url=url+keep_stub();
	var xmlhttp=load_XML_doc(url,null,post);
	if ((xmlhttp.responseText!='') && (xmlhttp.responseText.replace(/[ \t\n\r]/g,'')!='0'/*some cache layers may change blank to zero*/))
	{
		if (xmlhttp.responseText!='false')
		{
			if (xmlhttp.responseText.length>1000)
			{
				var error_window=window.open();
				if (error_window)
				{
					error_window.document.write(xmlhttp.responseText);
					error_window.document.close();
				}
			} else
			{
				window.alert(xmlhttp.responseText);
			}
		}
		return false;
	}
	return true;
}

function ajax_form_submit(event,form,block_name,map)
{
	if (typeof window.cleverFindValue=='undefined') return true;
	
	cancelBubbling(event);

	var comcode='[block'+map+']'+block_name+'[/block]';
	var post='data='+window.encodeURIComponent(comcode);
	for (var i=0;i<form.elements.length;i++)
	{
		post+='&'+form.elements[i].name+'='+window.encodeURIComponent(cleverFindValue(form,form.elements[i]));
	}
	var request=load_XML_doc(maintain_theme_in_link('{$FIND_SCRIPT_NOHTTP;,comcode_convert}'+keep_stub(true)),null,post);

	if ((request.responseText!='') && (request.responseText!=''))
	{
		if (request.responseText!='false')
		{
			var result_tags=request.responseXML.documentElement.getElementsByTagName("result");
			if ((result_tags) && (result_tags.length!=0))
			{
				var result=result_tags[0];
				var xhtml=merge_text_nodes(result.childNodes);

				var element_replace=form;
				while (element_replace.className!='form_ajax_target')
				{
					element_replace=element_replace.parentNode;
					if (!element_replace) return true; // Oh dear, target not found
				}

				setInnerHTML(element_replace,xhtml);

				window.alert('{!SUCCESS;}');

				return false; // We've handled it internally
			}
		}
	}
	
	return true;
}

function ajax_supported()
{
	// Intentionally not a single line, to help validator
	if ((typeof window.XMLHttpRequest!='undefined') || (typeof window.ActiveXObject!='undefined')) return true;
	return false;
}

function load_XML_doc(url,callback__method,post) // Note: 'post' is not an array, it's a string (a=b)
{
	var synchronous=!callback__method;

	if ((url.indexOf('://')==-1) && (url.substr(0,1)=='/'))
	{
		url=window.location.protocol+'//'+window.location.host+url;
	}

	if ((typeof window.AJAX_REQUESTS=="undefined") || (!window.AJAX_REQUESTS)) return null; // Probably the page is in process of being navigated away so window object is gone

	var index=AJAX_REQUESTS.length;
	AJAX_METHODS[index]=callback__method;
	if (typeof window.XMLHttpRequest!='undefined')
	{
		// Branch for none-IE
		AJAX_REQUESTS[index]=new XMLHttpRequest();
		if (!synchronous) AJAX_REQUESTS[index].onreadystatechange=process_request_changes;
		if (post)
		{
			AJAX_REQUESTS[index].open('POST',url,!synchronous);
			AJAX_REQUESTS[index].setRequestHeader('Content-Type','application/x-www-form-urlencoded');
			AJAX_REQUESTS[index].send(post);
		} else
		{
			AJAX_REQUESTS[index].open("GET",url,!synchronous);
			AJAX_REQUESTS[index].send(null);
		}
	}
	else if (typeof window.ActiveXObject!='undefined')
	{
		// Branch for IE
		AJAX_REQUESTS[index]=new ActiveXObject("Microsoft.XMLHTTP");
		if (AJAX_REQUESTS[index])
		{
			if (!synchronous) AJAX_REQUESTS[index].onreadystatechange=process_request_changes;
			if (post)
			{
				AJAX_REQUESTS[index].open('POST',url,!synchronous);
				AJAX_REQUESTS[index].setRequestHeader('Content-Type','application/x-www-form-urlencoded');
				try { AJAX_REQUESTS[index].send(post); } catch (e) {};
			} else
			{
				AJAX_REQUESTS[index].open("GET",url,!synchronous);
				try { AJAX_REQUESTS[index].send(); } catch (e) {};
			}
		}
	}

	if ((typeof window.AJAX_REQUESTS=="undefined") || (!window.AJAX_REQUESTS)) return null; // Probably the page is in process of being navigated away so window object is gone
	var result=AJAX_REQUESTS[index];
	if (synchronous)
	{
		AJAX_REQUESTS[index]=null;
	}
	return result;
}

function process_request_changes()
{
	if ((typeof window.AJAX_REQUESTS=="undefined") || (!window.AJAX_REQUESTS)) return; // Probably the page is in process of being navigated away so window object is gone

	// If any AJAX_REQUESTS are 'complete'
	var i,result;
	for (i=0;i<AJAX_REQUESTS.length;i++)
	{
		result=AJAX_REQUESTS[i];
		if ((result!=null) && (result.readyState) && (result.readyState==4))
		{
			AJAX_REQUESTS[i]=null;

			// If status is 'OK'
			if ((result.status) && (result.status==200) || (result.status==500) || (result.status==400) || (result.status==401))
			{
				//Process the result
				if ((AJAX_METHODS[i]) && (typeof AJAX_METHODS[i].accept_raw_response!='undefined')) return AJAX_METHODS[i](result);
				var xml=handle_errors_in_result(result);
				if (xml)
				{
					xml.validateOnParse=false;
					var ajax_result_frame=xml.documentElement;
					if (!ajax_result_frame) ajax_result_frame=xml;
					process_request_change(ajax_result_frame,i);
				}
			}
			else
			{
				try
				{
					if ((result.status==0) || (result.status==12029)) // 0 implies site down, or network down
					{
						if ((!window.network_down) && (!window.unloaded))
						{
							if (result.status==12029) window.alert("{!NETWORK_DOWN^#}");
							window.network_down=true;
						}
					} else
					{
						if (typeof console.log!='undefined') console.log("{!PROBLEM_RETRIEVING_XML^#}\n"+result.status+": "+result.statusText+".");
					}
				}
				catch (e)
				{
					if (typeof console.log!='undefined') console.log("{!PROBLEM_RETRIEVING_XML^#}");		// This is probably clicking back
				}
			}
		}
	}
}

function handle_errors_in_result(result)
{
	if ((result.responseXML==null) || (result.responseXML.childNodes.length==0))
	{
		// Try and parse again. Firefox can be weird.
		var xml;
		if (typeof DOMParser!="undefined")
		{
			try { xml=(new DOMParser()).parseFromString(result.responseText,"application/xml"); }
			catch(e) {}
		} else
		{
			var ieDOM=["MSXML2.DOMDocument","MSXML.DOMDocument","Microsoft.XMLDOM"];
			for (var i=0;i<ieDOM.length && !xml;i++) {
				try { xml=new ActiveXObject(ieDOM[i]);xml.loadXML(result.responseText); }
				catch(e) {}
			}
		}
		if (xml) return xml;

		if ((result.responseText) && (result.responseText!='') && (result.responseText.indexOf('<html')!=-1))
		{
			if (typeof console.debug!='undefined') console.debug(result);

			var error_window=window.open();
			if (error_window)
			{
				error_window.document.write(result.responseText);
				error_window.document.close();
			}
		}
		return false;
	}
	return result.responseXML;
}

function process_request_change(ajax_result_frame,i)
{
	if (!ajax_result_frame) return null; // Needed for Opera
	if ((typeof window.AJAX_REQUESTS=="undefined") || (!window.AJAX_REQUESTS)) return null; // Probably the page is in process of being navigated away so window object is gone

	if (ajax_result_frame.getElementsByTagName("message")[0])
	{
		//Either an error or a message was returned. :(
		var message=ajax_result_frame.getElementsByTagName("message")[0].firstChild.data;

		if (ajax_result_frame.getElementsByTagName("error")[0])
		{
			//It's an error :|
			window.alert("An error ("+ajax_result_frame.getElementsByTagName("error")[0].firstChild.data+") message was returned by the server: "+message);
			return null;
		}

		window.alert("An informational message was returned by the server: "+message);
		return null;
	}

	var ajax_result=ajax_result_frame.getElementsByTagName("result")[0];
	if (!ajax_result) return null;

	if ((ajax_result_frame.getElementsByTagName("method")[0]) || (AJAX_METHODS[i]))
	{
		var method=(ajax_result_frame.getElementsByTagName("method")[0])?eval('return '+merge_text_nodes(ajax_result_frame.getElementsByTagName("method")[0])):AJAX_METHODS[i];
		if (typeof method.response!='undefined') method.response(ajax_result_frame,ajax_result);
		else method(ajax_result_frame,ajax_result);

	}// else window.alert("Method required: as it is non-blocking");
	
	return null;
}

function create_xml_doc()
{
	var xml_doc;

	if (typeof window.ActiveXObject!='undefined')
	{
		xml_doc=new ActiveXObject("Microsoft.XMLDOM");
	}
	else if (document.implementation && document.implementation.createDocument)
	{
	  xml_doc=document.implementation.createDocument("","",null);
	}
	return xml_doc;
}

function merge_text_nodes(childNodes)
{
	var i,text='';
	for (i=0;i<childNodes.length;i++)
	{
		if (childNodes[i].nodeName=='#text')
		{
			text+=childNodes[i].data;
		}
	}
	return text;
}



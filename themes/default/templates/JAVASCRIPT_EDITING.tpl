"use strict";

/* Form editing code (general, may be used on many different kinds of form) */

// ===========
// HTML EDITOR
// ===========

function wysiwyg_on()
{
	var cookie=ReadCookie('use_wysiwyg');
	return ((cookie=='') || (cookie=='1')) && (browser_matches('wysiwyg') && ('{$MOBILE}'!='1'));
}

function toggle_wysiwyg(name)
{
	if (!browser_matches('wysiwyg'))
	{
		window.alert('{!TOGGLE_WYSIWYG_ERROR^;}');
		return false;
	}

	if (typeof window.load_XML_doc=='undefined') return false;
	if (typeof window.merge_text_nodes=='undefined') return false;
	if (typeof window.get_elements_by_class_name=='undefined') return false;

	var is_wysiwyg_on=wysiwyg_on();
	var saving_cookies=!is_wysiwyg_on || window.confirm('{!WHETHER_SAVE_WYSIWYG_SELECTION;}');
	if (saving_cookies)
	{
		SetCookie('use_wysiwyg',is_wysiwyg_on?'0':'1',3000);
	}

	var forms=document.getElementsByTagName('form');
	var counter,id;
	var so=document.getElementById('post_special_options');
	var so2=document.getElementById('post_special_options2');

	var request,result;
	if (is_wysiwyg_on)
	{
		// Find if the WYSIWYG has anything in it - if not, discard
		var all_empty=true,myregexp=new RegExp(/((\s)|(<p\d*\/>)|(<\/p>)|(<p>)|(&nbsp;)|(<br[^>]*>))*/);
		for (var fid=0;fid<forms.length;fid++)
		{
			for (counter=0;counter<forms[fid].elements.length;counter++)
			{
				id=forms[fid].elements[counter].id;
				if (typeof window.areaedit_editors[id]!='undefined')
				{
					if (window.areaedit_editors[id].getData().replace(myregexp,'')!='') all_empty=false;
				}
			}
		}

		// If we do not need to prompt, hard coded it with the "discard" line text, else make real prompt
		var prompt=all_empty?'{!DISCARD_WYSIWYG_CHANGES_LINE^;}':generate_question_ui('{!DISCARD_WYSIWYG_CHANGES_NICE^;}',{cancel: '{!INPUTSYSTEM_CANCEL^;}',convert: '{!DISCARD_WYSIWYG_CHANGES_LINE_CONVERT^;}',discard: '{!DISCARD_WYSIWYG_CHANGES_LINE^;}'},'{!DISABLE_WYSIWYG^;}','{!DISCARD_WYSIWYG_CHANGES^;}');
		if ((!prompt) || (prompt.toLowerCase()=='{!INPUTSYSTEM_CANCEL^;}'.toLowerCase()))
		{
			if (saving_cookies)
				SetCookie('use_wysiwyg','1',3000);
			return false;
		}
		var discard=(prompt.toLowerCase()=='{!DISCARD_WYSIWYG_CHANGES_LINE^;}'.toLowerCase());

		for (var fid=0;fid<forms.length;fid++)
		{
			for (counter=0;counter<forms[fid].elements.length;counter++)
			{
				id=forms[fid].elements[counter].id;
				if (typeof window.areaedit_editors[id]!='undefined')
				{
					var textarea=forms[fid].elements[counter];

					// Mark as non-WYSIWYG
					document.getElementById(id+'__is_wysiwyg').value='0';
					textarea.style.display='block';
					textarea.style.visibility='visible';
					textarea.disabled=false;
					textarea.readOnly=false;

					// Comcode conversion
					if ((discard) && (window.areaedit_original_comcode[id]))
					{
						textarea.value=window.areaedit_original_comcode[id];
					} else
					{
						request=load_XML_doc('{$FIND_SCRIPT_NOHTTP;,comcode_convert}?from_html=1'+keep_stub(),false,'data='+window.encodeURIComponent(window.areaedit_editors[id].getData()));
						if ((!request.responseXML) || (!request.responseXML.documentElement.getElementsByTagName("result")[0]))
						{
							textarea.value='[semihtml]'+areaedit_editors[id].getData()+'[/semihtml]';
						} else
						{
							var result_tags=request.responseXML.documentElement.getElementsByTagName("result");
							result=result_tags[0];
							textarea.value=merge_text_nodes(result.childNodes).replace(/\s*$/,'');
						}
						if ((textarea.value.indexOf('{\$,page hint: no_wysiwyg}')==-1) && (textarea.value!='')) textarea.value+='{\$,page hint: no_wysiwyg}';
					}
					if (document.getElementById('toggle_wysiwyg_'+id))
						setInnerHTML(document.getElementById('toggle_wysiwyg_'+id),'{!ENABLE_WYSIWYG^;}');

					// Unload editor
					window.areaedit_editors[id].elementMode=CKEDITOR.ELEMENT_MODE_NONE;
					CKEDITOR.remove(window.areaedit_editors[id]);
					delete window.areaedit_editors[id];
					var wysiwyg_node=document.getElementById('cke_'+id);
					wysiwyg_node.parentNode.removeChild(wysiwyg_node);
				}
			}
		}
		if (so) so.style.display='block';
		if (so2) so2.style.display='none';
	} else
	{
		if (so) so.style.display='none';
		if (so2) so2.style.display='block';

		for (var fid=0;fid<forms.length;fid++)
		{
			load_html_edit(forms[fid],true);
		}
	}

	return false;
}

var areaedit_editors=[];
var areaedit_original_comcode=[];
function load_html_edit(posting_form,ajax_copy)
{
	if (typeof window._editor_url=='undefined') return; {$,Probably caused by a JS error during initialisation}

	if ((!posting_form.getAttribute('method')) || (posting_form.getAttribute('method').toLowerCase()!='post')) return;
	
	if (!posting_form.elements['http_referer'])
	{
		var http_referer=document.createElement('input');
		http_referer.name='http_referer';
		http_referer.value=window.location.href;
		http_referer.setAttribute('type','hidden');
		posting_form.appendChild(http_referer);
	}
		
	if (typeof window.load_XML_doc=='undefined') return;
	if (typeof window.merge_text_nodes=='undefined') return;
	if (typeof window.CKEDITOR=='undefined') return;
	if (!browser_matches('wysiwyg')) return;
	if (!wysiwyg_on()) return;

	var so=document.getElementById('post_special_options');
	var so2=document.getElementById('post_special_options2');
	if ((!posting_form.elements['post']) || (posting_form.elements['post'].className.indexOf('wysiwyg')!=-1))
	{
		if (so) so.style.display='none';
		if (so2) so2.style.display='block';
	}

	var counter,count=0,e,indicator,those_done=[],result,request,id;
	for (counter=0;counter<posting_form.elements.length;counter++)
	{
		e=posting_form.elements[counter];
		id=e.id;

		if ((e.type=='textarea') && (e.className.indexOf('wysiwyg')!=-1) && (!is_comcode_xml(e)))
		{
			if (document.getElementById(id+'__is_wysiwyg'))
			{
				indicator=document.getElementById(id+'__is_wysiwyg');
			} else
			{
				indicator=document.createElement('input');
				indicator.setAttribute('type','hidden');
				indicator.id=e.id+'__is_wysiwyg';
				indicator.name=e.name+'__is_wysiwyg';
				posting_form.appendChild(indicator);
			}
			indicator.value='1';

			if (those_done[id]) continue;
			those_done[id]=1;

			count++;
			if (document.getElementById('toggle_wysiwyg_'+id))
				setInnerHTML(document.getElementById('toggle_wysiwyg_'+id),'{!DISABLE_WYSIWYG^;}');

			window.areaedit_original_comcode[id]=e.value;
			if (!ajax_copy)
			{
				if ((typeof posting_form.elements[id+"_parsed"]!='undefined') && (posting_form.elements[id+"_parsed"].value!='') && (e.defaultValue==e.value)) // The extra conditionals are for if back button used
					e.value=posting_form.elements[id+"_parsed"].value;
			} else
			{
				request=load_XML_doc('{$FIND_SCRIPT_NOHTTP;,comcode_convert}?semihtml=1&from_html=0'+keep_stub(),false,'data='+window.encodeURIComponent(posting_form.elements[counter].value));
				if (!request.responseXML)
				{
					posting_form.elements[counter].value='';
				} else
				{
					var result_tags=request.responseXML.documentElement.getElementsByTagName("result");
					if ((!result_tags) || (result_tags.length==0))
					{
						posting_form.elements[counter].value='';
					} else
					{
						result=result_tags[0];
						posting_form.elements[counter].value=merge_text_nodes(result.childNodes);
					}
				}
			}
			window.setTimeout(function(e,id) {
				return function() {
					window.areaedit_editors[id]=areaedit_init(e);
				}
			}(e,id),1000);
		}
	}
	if (count==0) return;
}

function areaedit_init(element)
{
	var pageStyleSheets=[];
	var linked_sheets=document.getElementsByTagName('link');
	for (var counter=0;counter<linked_sheets.length;counter++)
	{
		if (linked_sheets[counter].getAttribute('rel')=='stylesheet')
			pageStyleSheets.push(linked_sheets[counter].getAttribute('href'));
	}

	// Fiddly procedure to find our colour
	var test_div=document.createElement('div');
	document.body.appendChild(test_div);
	test_div.className='wysiwyg_color_finder';
	var wysiwyg_color=abstractGetComputedStyle(test_div,'color');
	test_div.parentNode.removeChild(test_div);

	// Carefully work out toolbar
	var precision_editing=(typeof get_elements_by_class_name(document.body,'comcode_button_box')[0]!='undefined'); // Look to see if this Comcode button is here as a hint whether we are doing an advanced editor. Unfortunately we cannot put contextual Tempcode inside a Javascript file, so this trick is needed.
	var toolbar=[];
	if (precision_editing)
		toolbar.push(['Source','-']);
	toolbar.push(['Cut','Copy','Paste',precision_editing?'PasteText':null,precision_editing?'PasteFromWord':null{+START,IF,{$VALUE_OPTION,commercial_spellchecker}},'-','SpellChecker', 'Scayt'{+END}]);
	toolbar.push(['Undo','Redo',precision_editing?'-':null,precision_editing?'Find':null,precision_editing?'Replace':null,'-',precision_editing?'SelectAll':null,'RemoveFormat']);
	toolbar.push(['Link','Unlink']);
	toolbar.push(precision_editing?'/':'-');
	var formatting=['Bold','Italic','Strike','-','Subscript','Superscript'];
	toolbar.push(formatting);
	toolbar.push(['NumberedList','BulletedList',precision_editing?'-':null,precision_editing?'Outdent':null,precision_editing?'Indent':null]);
	if (precision_editing)
		toolbar.push(['JustifyLeft','JustifyCenter','JustifyRight',precision_editing?'JustifyBlock':null]);
	toolbar.push([precision_editing?'Image':null,'Table']);
	if (precision_editing)
		toolbar.push('/');
	toolbar.push(['Format','Font','FontSize']);
	toolbar.push(['TextColor']);
	if (precision_editing)
		toolbar.push(['Maximize', 'ShowBlocks']);
	if (precision_editing)
		toolbar.push(['HorizontalRule','SpecialChar']);
	var use_ocportal_toolbar=true;
	if (use_ocportal_toolbar)
		toolbar.push(['ocportal_block','ocportal_comcode','ocportal_page','ocportal_quote','ocportal_box','ocportal_code']);

	var editor=CKEDITOR.replace(element.id, {
		enterMode : CKEDITOR.ENTER_BR,
		uiColor : wysiwyg_color,
		fontSize_sizes : '0.6em;0.85em;1em;1.1em;1.2em;1.3em;1.4em;1.5em;1.6em;1.7em;1.8em;2em',
		removePlugins: 'smiley,uicolor,contextmenu',
		extraPlugins: use_ocportal_toolbar?'ocportal':'',
		customConfig : '',
		bodyId : 'htmlarea',
		baseHref : get_base_url(),
		linkShowAdvancedTab : false,
		imageShowAdvancedTab : false,
		imageShowLinkTab : false,
		autoUpdateElement : true,
		contentsCss : pageStyleSheets,
		cssStatic : css,
		language : _editor_lang,
		emailProtection : false,
		resize_enabled : true,
		width : findWidth(element),
		height : (window.location.href.indexOf('cms_comcode_pages')==-1)?250:500,
		{+START,IF,{$NOT,{$VALUE_OPTION,commercial_spellchecker}}}
			disableNativeSpellChecker : false,
		{+END}
		toolbar : toolbar
	} );

	linked_sheets=document.getElementsByTagName('style');
	var css='';
	var global_div=document.getElementById('global_div');
	if (global_div) css='body { background-color: '+abstractGetComputedStyle(global_div,'background-color')+' !important; }';
	css+='body { width: 100%; min-height: 140px; }'; // IE9 selectability fix
	css+="#main_page_title { display: block !important }";
	css+=".MsoNormal { margin: 0; }";
	css+='.ocp_keep, .ocp_keep_block { font-weight: bold; background-color: #BABAFF; }';
	//css+='.ocp_keep_block[tag="block"] { display: block; padding: 30px 0; text-align: center; }';  Cutting and pasting becomes dodgy if this is enabled
	css+='.comcode_fake_table > div, .fp_col_block { outline: 1px dotted; margin: 1px 0; }';
	for (counter=0;counter<linked_sheets.length;counter++)
	{
		css+=getInnerHTML(linked_sheets[counter]);
	}
	editor.addCss(css);

	window.setTimeout( function() {
		window.scrollTo(0,0); // Otherwise jumps to last editor
	} , 500);

	editor.on('key', function (event) {
		if (typeof element.externalonKeyPress!='undefined')
		{
			element.value=editor.getData();
			element.externalonKeyPress(event,element);
		}
	} );

	editor.on('instanceReady', function (event) {
		findTagsInEditor(editor,element);
	} );
	window.setInterval(function() {
		if (isWYSIWYGField(element))
			findTagsInEditor(editor,element);
	}, 1000);

	// Weird issues in Chrome cutting+pasting blocks etc
	editor.on('paste', function (event) {
		if (event.data.html)
		{
			event.data.html=event.data.html.replace(/<meta charset="utf-8">/g,'');
			event.data.html=event.data.html.replace(/<br class="Apple-interchange-newline">/g,'<br>');
			event.data.html=event.data.html.replace(/<div style="text-align: center;"><font class="Apple-style-span" face="'Lucida Grande'"><span class="Apple-style-span" style="font-size: 11px; white-space: pre;"><br><\/span><\/font><\/div>$/,'<br><br>');
		}
	} );

	/*editor.on('instanceReady',function(ev) Does not work
	{
		var editor = ev.editor;
		if (typeof window.initialise_dragdrop_upload!='undefined') initialise_dragdrop_upload(element.id,element.id,editor.element.$.document);
	});*/

	return editor;
}

function findTagsInEditor(editor,element)
{
	if (!editor.document) return;
	if (typeof editor.document.$=='undefined') return;
	if (!editor.document.$) return;
	
	var comcodes=get_elements_by_class_name(editor.document.$.getElementsByTagName('body')[0],'(ocp_keep|ocp_keep_block|ocp_keep_real_block)');

	for (var i=0;i<comcodes.length;i++)
	{
		if (!comcodes[i].onmouseout)
		{
			comcodes[i].orig_title=comcodes[i].title;
			comcodes[i].onmouseout=function(event) { 
				if (!event) event=editor.window.$.event;

				var eventCopy={};
				if (event)
				{
					if (event.pageX) eventCopy.pageX=3000;
					if (event.clientX) eventCopy.clientX=3000;
					if (event.pageY) eventCopy.pageY=3000;
					if (event.clientY) eventCopy.clientY=3000;

					if (typeof window.deactivateTooltip!='undefined') deactivateTooltip(this,eventCopy);
				}
			};
			comcodes[i].onmousemove=function(event) {
				if (!event) event=editor.window.$.event;
				
				var eventCopy={};
				if (event)
				{
					if (event.pageX) eventCopy.pageX=3000;
					if (event.clientX) eventCopy.clientX=3000;
					if (event.pageY) eventCopy.pageY=3000;
					if (event.clientY) eventCopy.clientY=3000;

					if (typeof window.activateTooltip!='undefined')
					{
						repositionTooltip(this,eventCopy);
						this.title=this.orig_title;
					}
				}
			};
			comcodes[i].onmousedown=function(event) {
				if (!event) event=editor.window.$.event;
				
				if (event.altKey)
				{
					// Mouse cursor to start
					var range = selection.getRanges()[0];
					range.startOffset = 0;
					range.endOffset = 0;
					range.select()
					selection.selectRanges([range]);
				}
			}
			if (comcodes[i].nodeName.toLowerCase()=='input')
			{
				comcodes[i].onmouseup=function(event) {
					var field_name=editor.name;
					if ((typeof window.event!='undefined') && (window.event)) window.event.returnValue=false;
					var block_name=this.title.replace(/\[\/block\]$/,'').replace(/^(.|\s)*\]/,'');
					if (this.id=='') this.id='block_'+Math.round(Math.random()*10000000);
					var url='{$FIND_SCRIPT;,block_helper}?type=step2&block='+window.encodeURIComponent(block_name)+'&field_name='+field_name+'&parse_defaults='+window.encodeURIComponent(this.title)+'&save_to_id='+window.encodeURIComponent(this.id)+keep_stub();
					url=url+'&block_type='+(((field_name.indexOf('edit_panel_')==-1) && (window.location.href.indexOf(':panel_')==-1))?'main':'side');
					window.open(maintain_theme_in_link(url),'','width=750,height=600,status=no,resizable=yes,scrollbars=yes');
					return false;
				}
			}
			comcodes[i].onmouseover=function(event) {
				if (!event) event=editor.window.$.event;
				
				cancelBubbling(event);

				if (typeof window.activateTooltip!='undefined')
				{
					var tag_text='';
					if (this.nodeName.toLowerCase()=='input')
					{
						tag_text=this.orig_title;
					} else
					{
						tag_text=getInnerHTML(this);
					}
					//if (tag_text.match(/^\[.*\]$/))
					{
						this.style.cursor='pointer';
						
						var eventCopy={};
						if (event)
						{
							if (event.pageX) eventCopy.pageX=3000;
							if (event.clientX) eventCopy.clientX=3000;
							if (event.pageY) eventCopy.pageY=3000;
							if (event.clientY) eventCopy.clientY=3000;

							var self_ob=this;
							if ((typeof this.rendered_tooltip=='undefined') || (this.tag_text!=tag_text))
							{
								self_ob.is_over=true;

								var request=load_XML_doc(maintain_theme_in_link('{$FIND_SCRIPT_NOHTTP;,comcode_convert}?css=1&javascript=1&box_title={!PREVIEW;&}'+keep_stub(false)),function(ajax_result_frame,ajax_result) {
									if (ajax_result)
									{
										var tmp_rendered=getInnerHTML(ajax_result);
										if (tmp_rendered.indexOf('{!CCP_ERROR_STUB;}')==-1)
											self_ob.rendered_tooltip=tmp_rendered;
									}
									if (typeof self_ob.rendered_tooltip!='undefined')
									{
										self_ob.tag_text=tag_text;
										if (self_ob.is_over)
										{
											activateTooltip(self_ob,eventCopy,self_ob.rendered_tooltip,'auto');
											self_ob.title=self_ob.orig_title;
										}
									}
								},'data='+window.encodeURIComponent('[semihtml]'+tag_text.replace(/<\/?span[^>]*>/gi,'')).substr(0,1000)+'[/semihtml]');
							} else
							{
								activateTooltip(self_ob,eventCopy,self_ob.rendered_tooltip,'400px');
							}
						}
					}
				}
			};
		}
	}
}

// =============
// NORMAL EDITOR
// =============

function is_comcode_xml(element)
{
	return (element.value.substr(0,8)=='<comcode');
}

function convert_xml(name)
{
	if (typeof window.load_XML_doc=='undefined') return false;
	if (typeof window.merge_text_nodes=='undefined') return false;

	var element=document.getElementById(name);

	if (isWYSIWYGField(element))
	{
		window.alert('{!COMCODE_XML_CONVERT_NOT_WITH_WYSIWYG^;}');
		return false;
	}

	var old_text=element.value;
	var request=load_XML_doc('{$FIND_SCRIPT_NOHTTP;,comcode_convert}?to_comcode_xml=1'+keep_stub(),false,'data='+window.encodeURIComponent(old_text));
	var result=((request) && (request.responseXML) && (request.responseXML.documentElement))?request.responseXML.documentElement.getElementsByTagName("result")[0]:null;
	if ((result) && (result.childNodes[0].data)) element.value=merge_text_nodes(result.childNodes);
	else
	{
		var error_window=window.open();
		error_window.document.write(request.responseText);
		error_window.document.close();
		window.alert('{!COMCODE_XML_CONVERT_PARSE_ERROR^;}');
	}

	return false;
}

function do_emoticon(field_name,p,_opener)
{
	var element;
	if (_opener)
	{
		element=opener.document.getElementById(field_name);
	} else
	{
		element=document.getElementById(field_name);
	}
	element=ensure1_id(element,field_name);

	var title=p.title;
	title=title.replace(/^.*: /,'');

	var text=is_comcode_xml(element)?('<emoticon>'+escape_html(title)+'</emoticon>'):(' '+title+' ');

	if (_opener)
	{
		insertTextboxOpener(element,text,null,true,getInnerHTML(p));
	} else
	{
		insertTextbox(element,text,null,true,getInnerHTML(p));
	}
}

function do_attachment(field_name,id,description)
{
	if (!opener.areaedit_editors) return;

	if (!description) description='';

	var element=opener.document.getElementById(field_name);
	element=ensure1_id(element,field_name);

	var comcode;
	if (!is_comcode_xml(element))
	{
		comcode='\n\n[attachment type="island" description="'+escape_comcode(description)+'"]'+id+'[/attachment]';
	} else
	{
		comcode='<br /><br /><attachment type="island"><attachmentDescription>'+description+'</attachmentDescription>'+id+'</attachment>';
	}

	insertTextboxOpener(element,comcode);
}

function ensure1_id(element,field_name) // Works around IE bug
{
	var form=element.form;
	var i;
	for (i=0;i<form.elements.length;i++)
	{
		if ((form.elements[i].id==field_name)/* || (form.elements[i].name==field_name)*/)
		{
			return form.elements[i];
		}
	}
	return element;
}

function isWYSIWYGField(theElement)
{
	return ((typeof window.areaedit_editors!='undefined') && (theElement.id!='length') && (typeof areaedit_editors[theElement.id]!='undefined'));
}

function getTextbox(element)
{
	if (isWYSIWYGField(element))
	{
		var ret=areaedit_editors[element.id].getData();
		if ((ret=="\n") || (ret=="<br />")) ret="";
		return ret;
	}
	return element.value;
}

function setTextbox(element,text,html)
{
	if (isWYSIWYGField(element))
	{
		if (!html) html=escape_html(text).replace(new RegExp('\\\\n','gi'),'<br />');

		areaedit_editors[element.id].setData(html);
		fixImagesIn(areaedit_editors[element.id].document.getBody());

		window.setTimeout(function() {
			findTagsInEditor(areaedit_editors[element.id],element);
		}, 100);
	}

	element.value=text;
}

function insertTextbox(element,text,sel,plain_insert,html)
{
	if (isWYSIWYGField(element))
	{
		var insert='';
		if (plain_insert)
		{
			insert=getSelectedHTML(areaedit_editors[element.id])+(html?html:escape_html(text).replace(new RegExp('\\\\n','gi'),'<br />'));
		} else
		{
			var matches=text.match(/^\s*\[block(.*)\](.*)\[\/block\]\s*$/);
			if (matches)
			{
				insert=getSelectedHTML(areaedit_editors[element.id])+
					('<input class="ocp_keep_real_block" title="'+(html?matches[0].replace(/^\s*/,'').replace(/\s*$/,''):escape_html(matches[0].replace(/^\s*/,'').replace(/\s*$/,'')))+'" type="button" value="'+('{!comcode:COMCODE_EDITABLE_BLOCK;*}'.replace('\{1\}',matches[2]))+'" />');
			} else
			{
				var tag_name=text.replace(/^\[/,'').replace(/[ \]].*$/,'');
				insert=getSelectedHTML(areaedit_editors[element.id])+
					('<span title="'+(html?tag_name:escape_html(tag_name))+'" class="ocp_keep">')+
					(html?html:escape_html(text).replace(new RegExp('\\\\n','gi'),'<br />'))+
					'</span> ';
			}
		}
		areaedit_editors[element.id].focus();
		try
		{
			var before=areaedit_editors[element.id].getData();
			areaedit_editors[element.id].insertHtml(insert);
			var after=areaedit_editors[element.id].getData();
			if (after==before) throw "Failed to insert";
			findTagsInEditor(areaedit_editors[element.id],element);
			fixImagesIn(areaedit_editors[element.id].document.getBody());
		}
		catch (e) // Sometimes happens on Firefox in Windows, appending is a bit tamer (e.g. you cannot insert if you have the start of a h1 at cursor)
		{
			if (areaedit_editors[element.id].document)
			{
				setInnerHTML(areaedit_editors[element.id].document.getBody().$,insert,true);
				findTagsInEditor(areaedit_editors[element.id],element);
				fixImagesIn(areaedit_editors[element.id].document.getBody());
			} else
			{
				areaedit_editors[element.id].textarea.$.value+=text;
			}
		}
		return;
	}

	var from=element.value.length,to;

	element.focus();

	if (!sel) sel=document.selection?document.selection:null;

	if (typeof element.selectionEnd!='undefined') // Mozilla style
	{
		from=element.selectionStart;
		to=element.selectionEnd;

		var start=element.value.substring(0,from);
		var end=element.value.substring(to,element.value.length);

		element.value=start+element.value.substring(from,to)+text+end;
		setSelectionRange(element,from+text.length,from+text.length);
	} else
	if (sel) // IE style
	{
		var ourRange=sel.createRange();
		if ((ourRange.moveToElementText) || (ourRange.parentElement()==element))
		{
			if (ourRange.parentElement()!=element) ourRange.moveToElementText(element);
			ourRange.text=ourRange.text+text;
		} else
		{
			element.value+=text;
			from+=2;
			setSelectionRange(element,from+text.length,from+text.length);
		}
	}
	else
	{
		// :(
		from+=2;
		element.value+=text;
		setSelectionRange(element,from+text.length,from+text.length);
	}
}
function insertTextboxOpener(element,text,sel,plain_insert,html)
{
	if (!sel) sel=opener.document.selection?opener.document.selection:null;

	opener.insertTextbox(element,text,sel,plain_insert,html);
}

function getSelectedHTML(editor)
{
	var mySelection=editor.getSelection();
	if (!mySelection || mySelection.getType()==CKEDITOR.SELECTION_NONE) return '';

	var selectedText='';
	if (CKEDITOR.env.ie)
	{
		mySelection.unlock(true);
		selectedText=mySelection.getNative().createRange().htmlText;
	} else
	{
		try
		{
			selectedText=getInnerHTML(mySelection.getNative().getRangeAt(0).cloneContents());
		}
		catch (e) {};
	}
	return selectedText;
}

function insertTextboxWrapping(element,beforeWrapTag,afterWrapTag)
{
	var from,to;

	if (afterWrapTag=="")
	{
		if (!is_comcode_xml(element))
		{
			afterWrapTag="[/"+beforeWrapTag+"]";
			beforeWrapTag="["+beforeWrapTag+"]";
		} else
		{
			afterWrapTag="</"+beforeWrapTag+">";
			beforeWrapTag="<"+beforeWrapTag+">";
		}
	}

	if (isWYSIWYGField(element))
	{
		if (!browser_matches('opera')) areaedit_editors[element.id].focus(); // Needed on some browsers, but on Opera will defocus our selection
		var selectedHTML=getSelectedHTML(areaedit_editors[element.id]);
		if (browser_matches('opera')) areaedit_editors[element.id].getSelection().getNative().getRangeAt(0).deleteContents();
		areaedit_editors[element.id].insertHtml('<span title="'+escape_html(beforeWrapTag.replace(/^\[/,'').replace(/[ \]].*$/,''))+'" class="ocp_keep">'+beforeWrapTag+selectedHTML+afterWrapTag+'</span> ');
		fixImagesIn(areaedit_editors[element.id].document.getBody());
		findTagsInEditor(areaedit_editors[element.id],element);
		return;
	}

	if (typeof element.selectionEnd!='undefined') // Mozilla style
	{
		from=element.selectionStart;
		to=element.selectionEnd;

		var start=element.value.substring(0,from);
		var end=element.value.substring(to,element.value.length);

		if (to>from)
		{
			element.value=start+beforeWrapTag+element.value.substring(from,to)+afterWrapTag+end;
		} else
		{
			element.value=start+beforeWrapTag+afterWrapTag+end;
		}
		setSelectionRange(element,from,to+beforeWrapTag.length+afterWrapTag.length);
	} else
	if (typeof document.selection!='undefined') // IE style
	{
		element.focus();
		var sel=document.selection;
		var ourRange=sel.createRange();
		if ((ourRange.moveToElementText) || (ourRange.parentElement()==element))
		{
			if (ourRange.parentElement()!=element) ourRange.moveToElementText(element);
			ourRange.text=beforeWrapTag+ourRange.text+afterWrapTag;
		} else element.value+=beforeWrapTag+afterWrapTag;
	}
	else
	{
		// :(
		element.value+=beforeWrapTag+afterWrapTag;
		setSelectionRange(element,from,to+beforeWrapTag.length+afterWrapTag.length);
	}
}

// From http://www.faqts.com/knowledge_base/view.phtml/aid/13562
function setSelectionRange(input,selectionStart,selectionEnd)
{
	if (typeof input.setSelectionRange!='undefined') /* Mozilla style */
	{
		input.focus();
		input.setSelectionRange(selectionStart,selectionEnd);
	}
	else if (typeof input.createTextRange!='undefined') /* IE style */
	{
		var range=input.createTextRange();
		range.collapse(true);
		range.moveEnd('character',selectionEnd);
		range.moveStart('character',selectionStart);
		range.select();
	} else input.focus();
}
	  
